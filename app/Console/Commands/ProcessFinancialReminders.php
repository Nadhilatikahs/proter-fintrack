<?php

namespace App\Console\Commands;

use App\Events\ReminderCreated;
use App\Models\BudgetGoal;
use App\Models\Reminder;
use App\Models\ReminderSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AiReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessFinancialReminders extends Command
{
    protected $signature = 'fintrack:process-reminders';

    protected $description = 'Cek budget & goals dan buat reminder AI untuk tiap user';

    public function handle(AiReminderService $aiReminderService): int
    {
        $now = Carbon::now();

        // Ambil setting reminder tiap user (kalau tidak ada, skip user tsb)
        $settings = ReminderSetting::with('user')->get();

        foreach ($settings as $setting) {
            $user = $setting->user;

            if (! $user) {
                continue;
            }

            // 1) Proses budget (pembatas pengeluaran)
            $this->processBudgetReminders($user, $setting, $aiReminderService, $now);

            // 2) Proses goals (target jangka panjang, misal 10jt / 1 tahun)
            $this->processGoalReminders($user, $setting, $aiReminderService, $now);
        }

        $this->info('ProcessFinancialReminders selesai.');

        return self::SUCCESS;
    }

    protected function processBudgetReminders(
        User $user,
        ReminderSetting $setting,
        AiReminderService $ai,
        Carbon $now
    ): void {
        // Ambil semua BudgetGoal type 'budget' untuk user ini
        $budgets = BudgetGoal::where('user_id', $user->id)
            ->where('type', 'budget')
            ->whereNotNull('period_type')
            ->get();

        foreach ($budgets as $budget) {
            // Tentukan rentang waktu berdasarkan period_type
            [$start, $end, $periodDays] = $this->getPeriodRange($budget->period_type, $now);

            // Total pengeluaran (expense) dalam periode ini
            $spent = Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->sum('amount');

            if ($budget->target_amount <= 0) {
                continue;
            }

            $usage        = round(($spent / $budget->target_amount) * 100, 2);
            $daysPassed   = $start->diffInDays($now) + 1;
            $daysPassed   = max(1, $daysPassed);
            $timeProgress = round(($daysPassed / $periodDays) * 100, 2);

            $context = [
                'budget_goal_id' => $budget->id,
                'name'           => $budget->name,
                'description'    => $budget->description,
                'period_type'    => $budget->period_type,
                'target_amount'  => $budget->target_amount,
                'spent'          => $spent,
                'usage_percentage' => $usage,
                'time_progress_percentage' => $timeProgress,
                'period_start'   => $start->toDateString(),
                'period_end'     => $end->toDateString(),
                'days_passed'    => $daysPassed,
                'period_days'    => $periodDays,
            ];

            // 1a. Jika sudah > 100% → over limit
            if ($usage >= 100) {
                if ($this->alreadySentRecently($user, 'budget_over_limit', $budget->id)) {
                    continue;
                }

                $message = $ai->generateBudgetReminder(
                    user: $user,
                    type: 'over_limit',
                    context: $context
                );

                $reminder = Reminder::create([
                    'user_id'       => $user->id,
                    'type'          => 'budget_over_limit',
                    'related_id'    => $budget->id,
                    'related_model' => BudgetGoal::class,
                    'title'         => 'Budget kamu sudah kelewat batas 💸',
                    'message'       => $message,
                    'data'          => $context,
                ]);

                event(new ReminderCreated($reminder));
                continue;
            }

            // 1b. Kalau melewati threshold tapi belum limit
            if ($usage >= $setting->budget_warning_threshold) {
                if ($this->alreadySentRecently($user, 'budget_warning', $budget->id)) {
                    continue;
                }

                $message = $ai->generateBudgetReminder(
                    user: $user,
                    type: 'warning',
                    context: $context
                );

                $reminder = Reminder::create([
                    'user_id'       => $user->id,
                    'type'          => 'budget_warning',
                    'related_id'    => $budget->id,
                    'related_model' => BudgetGoal::class,
                    'title'         => 'Budget kamu udah kepake banyak nih 🧐',
                    'message'       => $message,
                    'data'          => $context,
                ]);

                event(new ReminderCreated($reminder));
            }
        }
    }

    /**
     * Hitung reminder untuk goals.
     */
    protected function processGoalReminders(
        User $user,
        ReminderSetting $setting,
        AiReminderService $ai,
        Carbon $now
    ): void {
        $goals = BudgetGoal::where('user_id', $user->id)
            ->where('type', 'goal')
            ->whereNotNull('target_date')
            ->get();

        foreach ($goals as $goal) {
            $daysLeft = $now->diffInDays($goal->target_date, false);

            if ($daysLeft < 0) {
                continue; // jika target sudah lewat
            }

            if ($daysLeft > $setting->goal_days_before_due) {
                continue; // belum masuk jangka reminder
            }

            if ($this->alreadySentRecently($user, 'goal_due_soon', $goal->id)) {
                continue;
            }

            // NOTE: untuk progress nominal, di versi sederhana ini
            // kita belum menghubungkan langsung dengan saving transaction,
            // jadi fokusnya ke countdown waktu.
            $context = [
                'budget_goal_id' => $goal->id,
                'name'           => $goal->name,
                'description'    => $goal->description,
                'target_amount'  => $goal->target_amount,
                'target_date'    => $goal->target_date->toDateString(),
                'days_left'      => $daysLeft,
            ];

            $message = $ai->generateGoalReminder(
                user: $user,
                context: $context
            );

            $reminder = Reminder::create([
                'user_id'       => $user->id,
                'type'          => 'goal_due_soon',
                'related_id'    => $goal->id,
                'related_model' => BudgetGoal::class,
                'title'         => 'Goal kamu makin deket nih ✨',
                'message'       => $message,
                'data'          => $context,
            ]);

            event(new ReminderCreated($reminder));
        }
    }

    /**
     * Tentukan rentang periode berdasarkan period_type.
     *
     * @return array [Carbon $start, Carbon $end, int $periodDays]
     */
    protected function getPeriodRange(string $periodType, Carbon $now): array
    {
        $start = $now->copy();
        $end   = $now->copy();

        return match ($periodType) {
            'daily'    => [
                $start->startOfDay(),
                $end->endOfDay(),
                1,
            ],
            'weekly'   => [
                $start->startOfWeek(),   // Senin
                $end->endOfWeek(),       // Minggu
                7,
            ],
            'biweekly' => [
                $start->copy()->subDays(13)->startOfDay(), // 14 hari terakhir
                $end->endOfDay(),
                14,
            ],
            'yearly'   => [
                $start->startOfYear(),
                $end->endOfYear(),
                $start->daysInYear,
            ],
            default    => [
                // default: monthly
                $start->startOfMonth(),
                $end->endOfMonth(),
                $start->daysInMonth,
            ],
        };
    }

    protected function alreadySentRecently(User $user, string $type, int $relatedId): bool
    {
        return Reminder::where('user_id', $user->id)
            ->where('type', $type)
            ->where('related_id', $relatedId)
            ->where('created_at', '>=', now()->subDay()) // 1x per 24 jam
            ->exists();
    }
}
