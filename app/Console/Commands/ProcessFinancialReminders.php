<?php

namespace App\Console\Commands;

use App\Events\ReminderCreated;
use App\Models\Budget;
use App\Models\Goal;
use App\Models\Reminder;
use App\Models\ReminderSetting;
use App\Models\User;
use App\Services\AiReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessFinancialReminders extends Command
{
    protected $signature = 'fintrack:process-reminders';

    protected $description = 'Cek budget/goal dan buat reminder AI untuk tiap user';

    public function handle(AiReminderService $aiReminderService): int
    {
        $now = Carbon::now();

        // Ambil semua user yang punya setting
        $settings = ReminderSetting::with('user')->get();

        foreach ($settings as $setting) {
            $user = $setting->user;

            if (! $user) {
                continue;
            }

            // 1) Budget reminder (bulan ini)
            $this->processBudgetReminders($user, $setting, $aiReminderService, $now);

            // 2) Goal reminder (mendekati jatuh tempo)
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
        $month = $now->month;
        $year  = $now->year;

        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        foreach ($budgets as $budget) {
            $usage = $budget->usage_percentage;

            $context = [
                'budget_id'        => $budget->id,
                'category'         => optional($budget->category)->name,
                'limit_amount'     => $budget->limit_amount,
                'spent'            => $budget->spent,
                'remaining'        => $budget->remaining,
                'usage_percentage' => $usage,
                'month'            => $month,
                'year'             => $year,
            ];

            // 1a. Kalau sudah >= 100% -> over_limit
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
                    'related_model' => Budget::class,
                    'title'         => 'Budget bulan ini sudah terlampaui',
                    'message'       => $message,
                    'data'          => $context,
                ]);

                // Trigger event => listener akan kirim email
                event(new ReminderCreated($reminder));

                continue;
            }

            // 1b. Budget mendekati batas (>= threshold)
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
                    'related_model' => Budget::class,
                    'title'         => 'Budget mendekati batas',
                    'message'       => $message,
                    'data'          => $context,
                ]);

                event(new ReminderCreated($reminder));
            }
        }
    }

    protected function processGoalReminders(
        User $user,
        ReminderSetting $setting,
        AiReminderService $ai,
        Carbon $now
    ): void {
        $goals = Goal::where('user_id', $user->id)
            ->whereColumn('current_amount', '<', 'target_amount')
            ->whereNotNull('target_date')
            ->get();

        foreach ($goals as $goal) {
            $daysLeft = $now->diffInDays($goal->target_date, false);

            if ($daysLeft < 0) {
                continue; // sudah lewat
            }

            if ($daysLeft > $setting->goal_days_before_due) {
                continue; // belum mendekati
            }

            if ($this->alreadySentRecently($user, 'goal_due_soon', $goal->id)) {
                continue;
            }

            $context = [
                'goal_id'          => $goal->id,
                'name'             => $goal->name,
                'target_amount'    => $goal->target_amount,
                'current_amount'   => $goal->current_amount,
                'progress_percent' => $goal->progress_percentage,
                'target_date'      => $goal->target_date->toDateString(),
                'days_left'        => $daysLeft,
            ];

            $message = $ai->generateGoalReminder(
                user: $user,
                context: $context
            );

            $reminder = Reminder::create([
                'user_id'       => $user->id,
                'type'          => 'goal_due_soon',
                'related_id'    => $goal->id,
                'related_model' => Goal::class,
                'title'         => 'Goal mendekati target date',
                'message'       => $message,
                'data'          => $context,
            ]);

            event(new ReminderCreated($reminder));
        }
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
