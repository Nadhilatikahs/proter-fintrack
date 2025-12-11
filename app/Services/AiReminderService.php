<?php

namespace App\Services;

use App\Models\BudgetGoal;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AiReminderService
{
    /**
     * Pesan reminder untuk budget / goal.
     * type: 'budget' atau 'goal'
     * return null jika progress < 50 (tidak perlu notif).
     */
    public function budgetGoalMessage(BudgetGoal $goal, string $type, int $progress): ?string
    {
        if ($progress < 50) {
            return null;
        }

        // Coba pakai AI kalau API key tersedia
        $aiMessage = $this->tryCallAiForBudgetGoal($goal, $type, $progress);

        if ($aiMessage) {
            return $aiMessage;
        }

        // Fallback rule-based kalau AI tidak tersedia / error
        return $this->ruleBasedBudgetGoalMessage($type, $progress);
    }

    protected function ruleBasedBudgetGoalMessage(string $type, int $progress): string
    {
        if ($type === 'budget') {
            if ($progress < 60) {
                return 'Pengeluaran kamu sudah sekitar 50% dari limit periode ini. Mulai pantau transaksi supaya tidak kebablasan.';
            } elseif ($progress < 70) {
                return 'Limit sudah terpakai lebih dari 60%. Coba tahan pengeluaran yang tidak terlalu penting.';
            } elseif ($progress < 80) {
                return 'Pengeluaran sudah di atas 70%. Identifikasi kategori mana yang bisa dipangkas.';
            } elseif ($progress < 90) {
                return 'Sudah lewat 80% limit. Risiko tembus limit sangat tinggi jika tidak dikendalikan.';
            } elseif ($progress < 100) {
                return 'Hampir 100% limit terpakai. Hindari pengeluaran non-prioritas di sisa periode ini.';
            }

            return 'Limit budget sudah terlampaui. Pertimbangkan revisi limit atau tunda pengeluaran berikutnya.';
        }

        // GOAL – nada motivasi
        if ($progress < 60) {
            return 'Tabungan kamu sudah mencapai sekitar 50% dari target. Pertahankan ritme nabungnya.';
        } elseif ($progress < 70) {
            return 'Target tabungan sudah lewat 60%. Sedikit lagi makin dekat dengan goal ini.';
        } elseif ($progress < 80) {
            return 'Tabungan sudah di atas 70%. Kalau konsisten, goal ini akan segera tercapai.';
        } elseif ($progress < 90) {
            return 'Sudah lewat 80% dari target. Ini saat yang bagus untuk mengunci jadwal nabung rutin.';
        } elseif ($progress < 100) {
            return 'Hampir 100%! Satu dorongan terakhir dan target ini akan tercapai.';
        }

        return 'Selamat! Target tabungan sudah tercapai. Kamu bisa mempertahankan kebiasaan ini atau set goal baru.';
    }

    /**
     * Pesan pengingat input transaksi harian.
     * $timeLabel misal: 'pagi' atau 'malam'
     */
    public function dailyTransactionMessage(User $user, string $timeLabel): string
    {
        // Bisa dibuat lebih personal kalau mau
        return match ($timeLabel) {
            'pagi'  => 'Jangan lupa input transaksi hari ini. Catat pengeluaran & pemasukan pagi ini supaya tidak lupa di akhir hari.',
            'malam' => 'Sudah cek transaksi hari ini? Input pengeluaran & pemasukan sekarang sebelum kamu lupa.',
            default => 'Jangan lupa input transaksi harian kamu hari ini.',
        };
    }

    /**
     * OPTIONAL: panggil OpenAI API untuk bikin pesan lebih “AI”.
     * Set `services.openai.key` di config kalau mau pakai.
     */
    protected function tryCallAiForBudgetGoal(BudgetGoal $goal, string $type, int $progress): ?string
    {
        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            return null;
        }

        $role = $type === 'budget' ? 'budget limit' : 'saving goal';

        $prompt = sprintf(
            "Buat satu kalimat singkat (maks 35 kata) dalam bahasa Indonesia, nada singkat dan jelas, sebagai reminder untuk user tentang %s dengan progress %d%%.
Nama: %s, target: Rp %s. Jangan pakai emoji.",
            $role,
            $progress,
            $goal->name,
            number_format($goal->target_amount ?? 0, 0, ',', '.')
        );

        try {
            $response = Http::withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4.1-mini', // atau model lain yang kamu pakai
                    'messages' => [
                        ['role' => 'system', 'content' => 'Kamu asisten finansial yang sangat ringkas.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 120,
                    'temperature' => 0.4,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $text = $response->json('choices.0.message.content');

            return $text ? trim($text) : null;
        } catch (\Throwable $e) {
            // Jangan sampai command mati hanya karena AI error
            return null;
        }
    }
}
