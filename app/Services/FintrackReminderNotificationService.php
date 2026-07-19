<?php

namespace App\Services;

use App\Models\BudgetGoal;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class FintrackReminderNotificationService
{
    public function __construct(
        protected NotificationAiService $aiService
    ) {
    }

    /**
     * Build daily transaction reminder message for a user.
     */
    public function buildDailyTransactionReminder(
        User $user,
        string $waktu,
        bool $hasTodayTransactions,
        ?int $unloggedCount = null
    ): string {
        try {
            $event = $waktu === 'pagi'
                ? 'DAILY_REMINDER_MORNING'
                : 'DAILY_REMINDER_EVENING';

            $context = [
                'user_name' => $user->name,
                'has_transactions' => $hasTodayTransactions,
                'waktu' => $waktu,
                'date' => now()->format('d F Y'),
                'unlogged_count' => $unloggedCount ?? 0,
            ];

            $result = $this->aiService->generate($event, $context);

            return $result['body']
                ?? $this->getFallbackDailyMessage($waktu);
        } catch (\Throwable $e) {
            Log::warning('Failed to generate daily AI reminder', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackDailyMessage($waktu);
        }
    }

    /**
     * Build budget / goal reminder message.
     */
    public function buildBudgetGoalReminder(BudgetGoal $goal, float $progress): string
    {
        try {
            $progress = round($progress, 1);

            if ($goal->type === 'budget') {
                $event = $progress >= 50 && $progress < 60
                    ? 'BUDGET_WARNING_50'
                    : 'BUDGET_WARNING';

                $context = [
                    'budget_name' => $goal->name,
                    'progress' => $progress,
                    'percent_used' => $progress,
                    'target_amount' => $goal->target_amount,
                    'limit' => $goal->target_amount,
                    'spent' => ($goal->target_amount * $progress) / 100,
                    'remaining' => $goal->target_amount - (($goal->target_amount * $progress) / 100),
                ];
            } else {
                if ($progress >= 100) {
                    $event = 'GOAL_COMPLETED';
                } elseif ($progress >= 90) {
                    $event = 'GOAL_PROGRESS_90';
                } elseif ($progress >= 80) {
                    $event = 'GOAL_PROGRESS_80';
                } elseif ($progress >= 50) {
                    $event = 'GOAL_PROGRESS_50';
                } else {
                    $event = 'GOAL_PROGRESS';
                }

                $current = ($goal->target_amount * $progress) / 100;

                $context = [
                    'goal_name' => $goal->name,
                    'progress' => $progress,
                    'target_amount' => $goal->target_amount,
                    'current' => $current,
                    'remaining' => $goal->target_amount - $current,
                ];
            }

            $result = $this->aiService->generate($event, $context);

            return $result['body']
                ?? $this->getFallbackBudgetGoalMessage($goal, $progress);
        } catch (\Throwable $e) {
            Log::warning('Failed to generate budget/goal AI reminder', [
                'goal_id' => $goal->id,
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackBudgetGoalMessage($goal, $progress);
        }
    }

    protected function getFallbackDailyMessage(string $waktu): string
    {
        return $waktu === 'pagi'
            ? 'Biar rapi sampai akhir bulan, isi transaksi hari ini ya.'
            : 'Sebelum rebahan, input transaksi hari ini ya.';
    }

    protected function getFallbackBudgetGoalMessage(BudgetGoal $goal, float $progress): string
    {
        return sprintf(
            '%s sudah %s%% tercapai. Tetap konsisten ya.',
            $goal->name,
            round($progress, 1)
        );
    }
}
