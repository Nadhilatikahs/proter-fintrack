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
     *
     * @param User $user
     * @param string $waktu 'pagi' or 'malam'
     * @param bool $hasTodayTransactions
     * @param int|null $unloggedCount Optional count of unlogged transactions
     * @return string
     */
    public function buildDailyTransactionReminder(
        User $user,
        string $waktu,
        bool $hasTodayTransactions,
        ?int $unloggedCount = null
    ): string {
        try {
            $event = $waktu === 'pagi' ? 'DAILY_REMINDER_MORNING' : 'DAILY_REMINDER_EVENING';

            $context = [
                'user_name' => $user->name,
                'has_transactions' => $hasTodayTransactions,
                'waktu' => $waktu,
                'date' => now()->format('d F Y'),
                'unlogged_count' => $unloggedCount ?? 0,
            ];

            $result = $this->aiService->generate($event, $context);
            return $result['body'] ?? $this->getFallbackDailyMessage($waktu, $hasTodayTransactions);
        } catch (\Throwable $e) {
            Log::warning('Failed to generate AI reminder', [
                'user_id' => $user->id,
                'waktu' => $waktu,
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackDailyMessage($waktu, $hasTodayTransactions);
        }
    }

    /**
     * Build budget/goal reminder message based on progress.
     *
     * @param BudgetGoal $goal
     * @param float $progress Percentage (0-100)
     * @return string
     */
    public function buildBudgetGoalReminder(BudgetGoal $goal, float $progress): string
    {
        try {
            $progressRounded = round($progress, 1);

            if ($goal->type === 'budget') {
                // Budget warnings - use BUDGET_WARNING_50 for 50% milestone
                if ($progressRounded >= 50 && $progressRounded < 60) {
                    $event = 'BUDGET_WARNING_50';
                } else {
                    $event = 'BUDGET_WARNING';
                }

                $context = [
                    'budget_name' => $goal->name,
                    'progress' => $progressRounded,
                    'percent_used' => $progressRounded,
                    'target_amount' => $goal->target_amount,
                    'limit' => $goal->target_amount,
                    'spent' => ($goal->target_amount * $progressRounded) / 100,
                    'remaining' => $goal->target_amount - (($goal->target_amount * $progressRounded) / 100),
                    'remaining_amount' => $goal->target_amount - (($goal->target_amount * $progressRounded) / 100),
                    'period_type' => $goal->period_type ?? 'monthly',
                ];
            } else {
                // Goal progress - determine milestone
                if ($progressRounded >= 100) {
                    $event = 'GOAL_COMPLETED';
                } elseif ($progressRounded >= 90) {
                    $event = 'GOAL_PROGRESS_90';
                } elseif ($progressRounded >= 80) {
                    $event = 'GOAL_PROGRESS_80';
                } elseif ($progressRounded >= 50) {
                    $event = 'GOAL_PROGRESS_50';
                } else {
                    $event = 'GOAL_PROGRESS';
                }

                $current = ($goal->target_amount * $progressRounded) / 100;
                $remaining = $goal->target_amount - $current;

                $context = [
                    'goal_name' => $goal->name,
                    'progress' => $progressRounded,
                    'target_amount' => $goal->target_amount,
                    'target' => $goal->target_amount,
                    'current' => $current,
                    'remaining' => $remaining,
                ];
            }

            $result = $this->aiService->generate($event, $context);
            return $result['body'] ?? $this->getFallbackBudgetGoalMessage($goal, $progress);
        } catch (\Throwable $e) {
            Log::warning('Failed to generate AI budget/goal reminder', [
                'goal_id' => $goal->id,
                'progress' => $progress,
                'error' => $e->getMessage(),
            ]);

            return $this->getFallbackBudgetGoalMessage($goal, $progress);
        }
    }

    /**
     * Fallback message for daily reminders - Using templates
     */
    protected function getFallbackDailyMessage(string $waktu, bool $hasTodayTransactions): string
    {
        if ($waktu === 'pagi') {
            // Morning templates from ai_notification_template.txt
            $templates = [
                'Biar rapi sampai akhir bulan, isi transaksi hari ini ya. 30 detik doang, serius.',
                'Yuk input transaksi. Biar grafikmu nggak "ngarang" dan saldo nggak misterius.',
                'Kalau ada jajan/ongkir/parkir kemarin, masukin dulu biar aman.',
            ];
            return $templates[array_rand($templates)];
        }

        // Evening templates from ai_notification_template.txt
        $templates = [
            'Sebelum rebahan, input transaksi hari ini ya. Biar besok hidup tanpa plot twist.',
            'Catat pengeluaran/pemasukan hari ini. Biar "kok habis ya?" nggak kejadian lagi.',
            'Quick check: transaksi hari ini udah masuk semua belum? Fintrack mau bantu kamu waras finansial.',
        ];
        return $templates[array_rand($templates)];
    }

    /**
     * Fallback message for budget/goal reminders - Using templates
     */
    protected function getFallbackBudgetGoalMessage(BudgetGoal $goal, float $progress): string
    {
        $name = $goal->name ?? ($goal->type === 'budget' ? 'Budget' : 'Goal');
        $progressRounded = round($progress, 1);

        if ($goal->type === 'budget') {
            // Budget warning templates
            $templates = [
                sprintf('Kamu udah pakai %s%% dari %s. Santai, tapi mulai "rem" dikit ya biar nggak kebablasan.', $progressRounded, $name),
                sprintf('%s udah kepake %s%%. Cek lagi yang "kecil-kecil" itu suka nyolong diam-diam.', $name, $progressRounded),
                sprintf('%s%% udah kepake. Mode: hemat tipis-tipis biar akhir bulan nggak survival mode.', $progressRounded),
                sprintf('%s udah %s%%. Kalau lanjut begini, nanti kita makan "mie prestige" lagi.', $name, $progressRounded),
            ];
            return $templates[array_rand($templates)];
        }

        // Goal progress templates based on milestone
        if ($progressRounded >= 100) {
            $templates = [
                sprintf('%s tembus 100%%! Kamu resmi menang lawan impulsif. Mau set goal baru?', $name),
                sprintf('Target %s kelar! Saatnya selebrasi yang tetap waras: pilih reward kecil, bukan balas dendam belanja.', $name),
            ];
        } elseif ($progressRounded >= 90) {
            $templates = [
                sprintf('%s udah %s%%. Jangan kena godaan "checkout dulu" ya.', $name, $progressRounded),
                sprintf('Kamu udah sejauh ini. 10%% terakhir biasanya paling licin—tetap fokus.', $name),
            ];
        } elseif ($progressRounded >= 80) {
            $templates = [
                sprintf('%s udah %s%%. Jangan lengah, ini fase "hampir jadi".', $name, $progressRounded),
                sprintf('%s udah 80%%+. Satu dua langkah lagi, langsung finish.', $name),
            ];
        } elseif ($progressRounded >= 50) {
            $templates = [
                sprintf('Mantap! %s tinggal separuh lagi. Konsisten dikit, nanti selesai tanpa drama.', $name),
                sprintf('%s udah %s%%. Kamu bukan cuma niat, kamu bukti.', $name, $progressRounded),
            ];
        } else {
            $templates = [
                sprintf('%s udah %s%%. Keep going!', $name, $progressRounded),
            ];
        }

        return $templates[array_rand($templates)];
    }
}

