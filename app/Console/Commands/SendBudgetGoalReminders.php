<?php

namespace App\Console\Commands;

use App\Models\BudgetGoal;
use App\Models\Transaction;
use App\Services\AiReminderService;
use Carbon\Carbon;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Console\Command;

class SendBudgetGoalReminders extends Command
{
    protected $signature = 'fintrack:budget-goal-reminders';

    protected $description = 'Kirim notifikasi AI untuk budget & goals berdasarkan progress (>=50%, setiap kelipatan 10%).';

    public function handle(AiReminderService $ai): int
    {
        // Ambil semua budget & goal yang punya target_amount > 0 dan user_id tidak null
        $goals = BudgetGoal::whereNotNull('user_id')
            ->where('target_amount', '>', 0)
            ->get();

        $now = Carbon::now();

        foreach ($goals as $goal) {
            $type = $goal->type === 'budget' ? 'budget' : 'goal';

            $progress = $type === 'budget'
                ? $this->calculateBudgetProgress($goal, $now)
                : $this->calculateGoalProgress($goal);

            $progress = (int) round(min(100, max(0, $progress)));

            // Lewati kalau masih di bawah 50%
            if ($progress < 50) {
                continue;
            }

            // Tetapkan threshold kelipatan 10 (50,60,70,...)
            $threshold = (int) (floor($progress / 10) * 10);

            // Kalau sudah pernah kirim di level ini atau lebih tinggi → skip
            if ($goal->last_notified_progress !== null
                && $goal->last_notified_progress >= $threshold) {
                continue;
            }

            // Bangun pesan
            $message = $ai->budgetGoalMessage($goal, $type, $progress);

            if (! $message) {
                continue;
            }

            // Kirim ke notifikasi Filament (database)
            $user = $goal->user; // pastikan relasi user() ada di model BudgetGoal

            if (! $user) {
                continue;
            }

            FilamentNotification::make()
                ->title($type === 'budget' ? 'Budget reminder' : 'Goal reminder')
                ->body($message)
                ->icon($type === 'budget'
                    ? 'heroicon-o-exclamation-triangle'
                    : 'heroicon-o-star')
                ->success() // tampil warna hijau; kalau mau warning pakai ->warning()
                ->sendToDatabase($user);

            // Update progress terakhir yang sudah dinotifikasi
            $goal->last_notified_progress = $threshold;
            $goal->save();
        }

        $this->info('Budget & goal reminders processed.');

        return self::SUCCESS;
    }

    protected function calculateBudgetProgress(BudgetGoal $budget, Carbon $now): float
    {
        [$start, $end] = $this->getPeriodRange($budget->period_type ?? 'monthly', $now);

        $spent = Transaction::where('user_id', $budget->user_id)
            ->where('type', 'expense')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        if ($budget->target_amount <= 0) {
            return 0;
        }

        return ($spent / $budget->target_amount) * 100;
    }

    protected function calculateGoalProgress(BudgetGoal $goal): float
    {
        if ($goal->target_amount <= 0) {
            return 0;
        }

        $saved = Transaction::where('user_id', $goal->user_id)
            ->where('type', 'income')
            ->when($goal->category_id, fn ($q, $categoryId) =>
                $q->where('category_id', $categoryId)
            )
            ->sum('amount');

        return ($saved / $goal->target_amount) * 100;
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
