<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class AiReminderService
{
    public function generateBudgetReminder(User $user, string $type, array $context): string
    {
        $prompt = $this->buildBudgetPrompt($user, $type, $context);

        return $this->callAiApi($prompt);
    }

    public function generateGoalReminder(User $user, array $context): string
    {
        $prompt = $this->buildGoalPrompt($user, $context);

        return $this->callAiApi($prompt);
    }

    protected function buildBudgetPrompt(User $user, string $type, array $c): string
    {
        $jenis      = $type === 'over_limit' ? 'MELEBIHI BATAS' : 'MENDekati batas';
        $name       = $c['name'] ?? 'Budget';
        $periodType = $c['period_type'] ?? 'monthly';

        $target   = number_format((float) ($c['target_amount'] ?? 0), 0, ',', '.');
        $spent    = number_format((float) ($c['spent'] ?? 0), 0, ',', '.');
        $usage    = $c['usage_percentage'] ?? 0;
        $timeProg = $c['time_progress_percentage'] ?? 0;
        $start    = $c['period_start'] ?? '-';
        $end      = $c['period_end'] ?? '-';
        $daysPass = $c['days_passed'] ?? 0;
        $period   = $c['period_days'] ?? 0;

        $periodLabel = match ($periodType) {
            'daily'    => 'per hari',
            'weekly'   => 'per minggu',
            'biweekly' => 'per 2 minggu',
            'yearly'   => 'per tahun',
            default    => 'per bulan',
        };

        return <<<PROMPT
Aku adalah asisten keuangan pribadi yang GAYA BAHASANYA:
- santai, gen Z, tapi tetap sopan dan tidak kasar,
- boleh pakai sedikit kata gaul (misalnya "anjay", "santuy", "gaskeun") dan emoji,
- TIDAK boleh menggunakan kata-kata menghina, SARA, atau vulgar.

Nama pengguna: {$user->name}

Situasi budget:
- Nama budget: {$name}
- Jenis budget: {$periodLabel}
- Jenis reminder: {$jenis}
- Periode budget: {$start} s/d {$end} (total sekitar {$period} hari)
- Batas budget (target_amount): Rp {$target}
- Sudah terpakai (spent): Rp {$spent}
- Persentase pemakaian (usage_percentage): {$usage}%
- Progress waktu periode (time_progress_percentage): {$timeProg}% (misalnya baru {$daysPass} hari berjalan)

Tugasmu:
- Tulis 1 PARAGRAF SINGKAT dalam bahasa Indonesia yang:
  - gaya santai dan friendly, cocok buat anak muda,
  - menyebutkan kondisi pemakaian budget (misalnya "budget kamu udah kepake 50%"),
  - kalau usage lebih besar dari progress waktu (pemakaian lebih ngebut dari waktunya), kamu boleh kasih peringatan lucu,
  - beri 1–2 saran praktis (misalnya "coba tahan dulu jajan kopi" atau "review pengeluaran yang nggak terlalu penting"),
  - maksimal 3 kalimat,
  - boleh pakai 2–4 emoji yang relevan.

Jangan tulis intro seperti "Halo, berikut ini adalah..." langsung ke pesan. Jangan menyebut bahwa kamu adalah AI.
PROMPT;
    }

    protected function buildGoalPrompt(User $user, array $c): string
    {
        $target     = number_format((float) ($c['target_amount'] ?? 0), 0, ',', '.');
        $name       = $c['name'] ?? 'Goal';
        $targetDate = $c['target_date'] ?? '-';
        $daysLeft   = $c['days_left'] ?? 0;

        return <<<PROMPT
Aku adalah asisten keuangan pribadi dengan gaya santai dan suportif.

GAYA BAHASA:
- santai, sedikit gen Z, tapi tetap sopan,
- boleh pakai emoji (2–4 emoji maksimal),
- jangan pakai kata kasar atau menghina.

Info goal:
- Nama goal: {$name}
- Target dana: Rp {$target}
- Target date: {$targetDate}
- Sisa hari menuju target: {$daysLeft} hari

Tugas:
- Tulis 1 paragraf pendek dalam bahasa Indonesia yang:
  - memberi semangat bahwa goal ini makin deket,
  - mengingatkan bahwa waktu makin sedikit (kalau sisa harinya sedikit),
  - menyarankan langkah kecil yang bisa dilakukan (misalnya nabung rutin tiap minggu),
  - maksimal 3 kalimat,
  - cocok untuk user bernama {$user->name} (boleh sebut namanya sekali).

Jangan tulis "Halo saya AI", langsung ke isi pesan.
PROMPT;
    }

    protected function callAiApi(string $prompt): string
    {
        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            // fallback kalau belum setting API key
            return '📌 Reminder keuangan: tunggu sebentar yach, budget & goal kamu lagi dicek sistem nich. Coba intip dashboard Fintrack dulu ya biar nggak kebablasan 😉';
        }

        try {
            $response = Http::withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'    => 'gpt-4.1-mini',
                    'messages' => [
                        [
                            'role'    => 'system',
                            'content' => 'Hi, aku asisten keuangan pribadi kamu, gaya santai, sedikit gen Z, tapi tetap sopan. Tidak boleh kasar atau menghina.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => 200,
                ]);

            if (! $response->successful()) {
                return '📌 Reminder keuangan: sistem gagal menghubungi layanan AI, tapi budget/goal kamu lagi rawan nih. Coba cek Fintrack dulu ya 🙏';
            }

            $text = $response->json('choices.0.message.content');

            return $text ? trim($text) : '📌 Reminder keuangan otomatis dari Fintrack.';
        } catch (\Throwable $e) {
            return '📌 Reminder keuangan: ada kendala teknis ringan. Untuk sementara, cek lagi pengeluaran dan budget kamu di Fintrack ya 🙌';
        }
    }
}
