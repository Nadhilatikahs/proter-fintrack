<?php

namespace App\Console\Commands;

use App\Models\BudgetGoal;
use App\Models\Transaction;
use App\Services\FintrackReminderNotificationService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FintrackBudgetGoalRemindersCommand extends Command
{
    protected $signature   = 'fintrack:budget-goal-reminders';
    protected $description = 'Kirim reminder AI untuk budget & goals berdasarkan progress.';

    public function __construct(
        protected FintrackReminderNotificationService $reminderService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // Ambil semua budget/goals yang punya target dan user_id
        $goals = BudgetGoal::query()
            ->whereNotNull('user_id')
            ->where('target_amount', '>', 0)
            ->get();

        $this->info('Processing ' . $goals->count() . ' budget/goals ...');

        foreach ($goals as $goal) {
            $user = $goal->user;
            if (! $user) {
                continue;
            }

            // Hitung progress
            $progress = $this->calculateProgress($goal);
            $lastNotified = $goal->last_notified_progress ?? null;

            // Logika threshold: mulai dari 50%, lalu setiap +10%
            if ($progress < 50) {
                continue;
            }

            // misal terakhir notify 60%, sekarang 63% -> jangan kirim dulu
            if ($lastNotified !== null && $progress < $lastNotified + 10) {
                continue;
            }

            DB::beginTransaction();

            try {
                $message = $this->reminderService->buildBudgetGoalReminder($goal, $progress);

                Notification::make()
                    ->title('Budget & Goal Reminder')
                    ->body($message)
                    ->icon('heroicon-o-bell-alert')
                    ->iconColor('warning')
                    ->sendToDatabase($user);

                $goal->last_notified_progress = (int) floor($progress);
                $goal->save();

                DB::commit();

                $this->info("Notified user {$user->id} for goal #{$goal->id} (progress {$progress}%).");
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("Failed goal #{$goal->id}: " . $e->getMessage());
            }
        }

        $this->info('Budget & goal reminders processed.');
        return self::SUCCESS;
    }

    /**
     * Hitung progress untuk budget atau goal.
     * Budget: pakai pengeluaran periode berjalan.
     * Goal: pakai income terkait (sesuai logika kamu sebelumnya).
     */
    protected function calculateProgress(BudgetGoal $goal): float
    {
        if (! $goal->target_amount || $goal->target_amount <= 0) {
            return 0;
        }

        $userId = $goal->user_id;

        if ($goal->type === 'budget') {
            $now = Carbon::now();
            [$start, $end] = $this->getPeriodRange($goal->period_type ?? 'monthly', $now);

            $spent = Transaction::query()
                ->where('user_id', $userId)
                ->where('type', 'expense')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->sum('amount');

            return round(($spent / $goal->target_amount) * 100, 1);
        }

        // goal tabungan: pakai income, bisa difilter category_id kalau perlu
        $saved = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->when($goal->category_id ?? null, fn ($q, $categoryId) =>
                $q->where('category_id', $categoryId)
            )
            ->sum('amount');

        return round(($saved / $goal->target_amount) * 100, 1);
    }

    protected function getPeriodRange(string $periodType, Carbon $now): array
    {
        $start = $now->copy();
        $end   = $now->copy();

        return match ($periodType) {
            'daily'    => [$start->startOfDay(), $end->endOfDay()],
            'weekly'   => [$start->startOfWeek(), $end->endOfWeek()],
            'biweekly' => [$start->copy()->subDays(13)->startOfDay(), $end->endOfDay()],
            'yearly'   => [$start->startOfYear(), $end->endOfYear()],
            default    => [$start->startOfMonth(), $end->endOfMonth()],
        };
    }
}
