<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class AiReminderService
{
    /**
     * Generate pesan reminder untuk budget (warning / over-limit).
     *
     * @param  User   $user    User pemilik budget
     * @param  string $type    'warning' atau 'over_limit'
     * @param  array  $context Data situasi budget (limit, spent, sisa, dll.)
     *
     * @return string Pesan reminder yang sudah di-generate AI / fallback
     */
    public function generateBudgetReminder(User $user, string $type, array $context): string
    {
        $prompt = $this->buildBudgetPrompt($user, $type, $context);

        return $this->callAiApi($prompt);
    }

    /**
     * Generate pesan reminder untuk financial goal yang mendekati due date.
     *
     * @param  User  $user
     * @param  array $context
     *
     * @return string
     */
    public function generateGoalReminder(User $user, array $context): string
    {
        $prompt = $this->buildGoalPrompt($user, $context);

        return $this->callAiApi($prompt);
    }

    /**
     * Susun prompt untuk budget reminder.
     */
    protected function buildBudgetPrompt(User $user, string $type, array $c): string
    {
        $jenis = $type === 'over_limit' ? 'MELEBIHI' : 'MENDekati';
        $category = $c['category'] ?? 'semua kategori';

        // Biar angka lebih rapi di prompt
        $limit      = number_format((float) ($c['limit_amount'] ?? 0), 0, ',', '.');
        $spent      = number_format((float) ($c['spent'] ?? 0), 0, ',', '.');
        $remaining  = number_format((float) ($c['remaining'] ?? 0), 0, ',', '.');
        $percentage = $c['usage_percentage'] ?? 0;

        return <<<PROMPT
Kamu adalah asisten keuangan pribadi yang ramah dan ringkas.

Nama pengguna: {$user->name}

Situasi:
- Jenis reminder: BUDGET {$jenis}
- Kategori: {$category}
- Batas budget: Rp {$limit}
- Sudah terpakai: Rp {$spent} ({$percentage}% dari budget)
- Sisa budget: Rp {$remaining}
- Periode: {$c['month']}/{$c['year']}

Tulis SATU paragraf pendek dalam bahasa Indonesia yang:
- berbahasa sopan tapi santai,
- menjelaskan kondisi budget saat ini,
- memberi 1–2 saran praktis (misalnya: review pengeluaran, kurangi kategori tertentu),
- maksimal 3 kalimat.

PROMPT;
    }

    /**
     * Susun prompt untuk goal reminder.
     */
    protected function buildGoalPrompt(User $user, array $c): string
    {
        $target      = number_format((float) ($c['target_amount'] ?? 0), 0, ',', '.');
        $current     = number_format((float) ($c['current_amount'] ?? 0), 0, ',', '.');
        $progress    = $c['progress_percent'] ?? 0;
        $targetDate  = $c['target_date'] ?? '-';
        $daysLeft    = $c['days_left'] ?? 0;

        return <<<PROMPT
Kamu adalah asisten keuangan pribadi yang membantu pengguna fokus pada tujuan keuangan.

Nama pengguna: {$user->name}

Situasi goal:
- Nama goal: {$c['name']}
- Target dana: Rp {$target}
- Terkumpul: Rp {$current} ({$progress}% tercapai)
- Target tanggal: {$targetDate}
- Sisa hari menuju target: {$daysLeft} hari

Tulis SATU paragraf pendek dalam bahasa Indonesia yang:
- memberi semangat,
- mengingatkan bahwa target semakin dekat,
- menyarankan langkah kecil yang bisa dilakukan (misalnya menambah sedikit tabungan mingguan),
- maksimal 3 kalimat.

PROMPT;
    }

    /**
     * Panggil API AI (contoh: OpenAI). Kalau tidak ada API key, pakai fallback.
     *
     * Di sini kamu boleh ganti ke provider apapun.
     */
    protected function callAiApi(string $prompt): string
    {
        // Ambil API key dari config/services.php -> 'openai'
        $apiKey = config('services.openai.key');

        // Fallback sederhana kalau belum set API key
        if (! $apiKey) {
            // Ambil sedikit isi prompt & jadikan pesan standar
            return '📌 Reminder keuangan otomatis (mode sederhana): '
                . 'sistem mendeteksi kondisi budget/goal yang perlu kamu perhatikan. '
                . 'Silakan cek detail di halaman laporan Fintrack.';
        }

        try {
            $response = Http::withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'    => 'gpt-4.1-mini',
                    'messages' => [
                        [
                            'role'    => 'system',
                            'content' => 'Kamu adalah asisten keuangan pribadi yang ramah dan ringkas.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => 200,
                ]);

            if (! $response->successful()) {
                // Bisa ditambah logging di sini
                return '📌 Reminder keuangan: sistem gagal menghubungi layanan AI, '
                    . 'namun budget/goal kamu perlu dicek. Buka aplikasi Fintrack untuk detailnya.';
            }

            $text = $response->json('choices.0.message.content');

            return $text ? trim($text) : '📌 Reminder keuangan otomatis.';
        } catch (\Throwable $e) {
            // Jangan sampai bikin command gagal total, cukup fallback
            return '📌 Reminder keuangan: ada kendala teknis saat membuat pesan AI. '
                . 'Silakan cek ringkasan budget dan goal di aplikasi Fintrack.';
        }
    }
}
