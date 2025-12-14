<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationAiService
{
    protected string $endpoint;
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->endpoint = 'https://api.openai.com/v1/chat/completions';
        $this->apiKey = config('services.openai.key');
        $this->model = config('ai.model');
    }

    /**
     * Generate notification copy via AI
     */
    public function generate(string $event, array $context): array
    {
        try {
            $prompt = $this->buildPrompt($event, $context);

            // Build HTTP client with SSL options for local development
            $http = Http::withToken($this->apiKey)
                ->timeout(config('ai.timeout', 15));

            // Disable SSL verification in local environment (Windows SSL issue)
            if (app()->environment('local')) {
                $http = $http->withOptions([
                    'verify' => false,
                ]);
            }

            $response = $http->post($this->endpoint, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional notification copywriter.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

            if (!$response->successful()) {
                Log::error('AI API response error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('AI request failed: ' . $response->status() . ' - ' . $response->body());
            }

            $content = $response->json('choices.0.message.content');

            $parsed = json_decode($content, true);

            if (!isset($parsed['title'], $parsed['body'])) {
                throw new Exception('Invalid AI response format');
            }

            return [
                'title' => trim($parsed['title']),
                'body' => trim($parsed['body']),
                'source' => 'ai',
            ];

        } catch (Exception $e) {
            Log::warning('Notification AI failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            return $this->fallback($event, $context);
        }
    }

    /**
     * Prompt builder per event - Using template from ai_notification_template.txt
     */
    protected function buildPrompt(string $event, array $context): string
    {
        return match ($event) {

            'BUDGET_WARNING_50' => $this->buildBudgetWarning50Prompt($context),

            'DAILY_REMINDER_MORNING' => $this->buildMorningReminderPrompt($context),

            'DAILY_REMINDER_EVENING' => $this->buildEveningReminderPrompt($context),

            'BUDGET_WARNING' => $this->buildBudgetWarningPrompt($context),

            'GOAL_PROGRESS_50' => $this->buildGoalProgress50Prompt($context),
            'GOAL_PROGRESS_80' => $this->buildGoalProgress80Prompt($context),
            'GOAL_PROGRESS_90' => $this->buildGoalProgress90Prompt($context),

            'GOAL_PROGRESS' => $this->buildGoalProgressPrompt($context),

            'GOAL_COMPLETED' => $this->buildGoalCompletedPrompt($context),

            default => throw new Exception("Unknown event: {$event}")
        };
    }

    /**
     * Budget Warning 50% - Template A/B/C
     */
    protected function buildBudgetWarning50Prompt(array $context): string
    {
        $budgetName = $context['budget_name'] ?? 'Budget';
        $percent = $context['percent_used'] ?? $context['progress'] ?? 50;
        $spent = $context['spent'] ?? 0;
        $limit = $context['limit'] ?? $context['target_amount'] ?? 0;
        $remaining = $context['remaining'] ?? $context['remaining_amount'] ?? 0;

        return <<<PROMPT
You are a notification copywriter for Fintrack, a personal finance app with gen-z friendly tone.

TASK: Write a budget alert notification (choose style A, B, or C randomly).

CONTEXT:
- Budget name: {$budgetName}
- Usage: {$percent}%
- Spent: {$spent}
- Limit: {$limit}
- Remaining: {$remaining}

TEMPLATE OPTIONS:

A. Friendly-warning:
   - Title: "Budget kamu udah setengah jalan 🧾"
   - Body: "Kamu udah pakai {$percent}% dari {$budgetName}. Santai, tapi mulai 'rem' dikit ya biar nggak kebablasan."
   OR
   - Title: "Half-time! 🚦"
   - Body: "{$budgetName} udah kepake {$percent}%. Cek lagi yang 'kecil-kecil' itu suka nyolong diam-diam."

B. Lebih tegas (tapi tetap asik):
   - Title: "Budget mulai panas 🔥"
   - Body: "{$percent}% udah kepake. Mode: hemat tipis-tipis biar akhir bulan nggak survival mode."
   OR
   - Title: "Dompet: 'pls no' 😭"
   - Body: "{$budgetName} udah {$percent}%. Kalau lanjut begini, nanti kita makan 'mie prestige' lagi."

C. Dengan saran aksi:
   - Title: "Reminder kecil tapi ngaruh 📌"
   - Body: "Budget {$budgetName} udah {$percent}%. Coba: stop impuls 24 jam + pilih 1 pengeluaran yang dipangkas hari ini."

RULES:
- Language: Indonesian
- Tone: gen-z, friendly, calm, no shaming
- Max 120 characters for body
- Max 1 emoji in title
- Use one of the template styles above, but feel free to adapt slightly

Return JSON only:
{"title":"","body":""}
PROMPT;
    }

    /**
     * Morning Reminder (09:00 WIB)
     */
    protected function buildMorningReminderPrompt(array $context): string
    {
        $unloggedCount = $context['unlogged_count'] ?? 0;
        $date = $context['date'] ?? now()->format('d F Y');
        $hasTransactions = $context['has_transactions'] ?? false;
        $hasTransactionsText = $hasTransactions ? 'yes' : 'no';

        $unloggedText = $unloggedCount > 0
            ? "Hari ini kamu ada {$unloggedCount} transaksi yang belum dicatat. "
            : "";

        return <<<PROMPT
You are a notification copywriter for Fintrack, a personal finance app with gen-z friendly tone.

TASK: Write a morning reminder notification (09:00 WIB) to input daily transactions.

CONTEXT:
- Date: {$date}
- Has transactions today: {$hasTransactionsText}
- Unlogged count: {$unloggedCount}

TEMPLATE OPTIONS (choose one style):
- Title: "Pagi bestie, catat dulu ☕"
  Body: "Biar rapi sampai akhir bulan, isi transaksi hari ini ya. 30 detik doang, serius."

- Title: "Checklist pagi ✅"
  Body: "Yuk input transaksi. Biar grafikmu nggak 'ngarang' dan saldo nggak misterius."

- Title: "Fintrack ping kamu 📲"
  Body: "Kalau ada jajan/ongkir/parkir kemarin, masukin dulu biar aman."

RULES:
- Language: Indonesian
- Tone: light, friendly, gen-z
- Max 110 characters for body
- Max 1 emoji in title
- {$unloggedText}If unlogged_count > 0, mention it naturally

Return JSON only:
{"title":"","body":""}
PROMPT;
    }

    /**
     * Evening Reminder (19:00 WIB)
     */
    protected function buildEveningReminderPrompt(array $context): string
    {
        $unloggedCount = $context['unlogged_count'] ?? 0;
        $date = $context['date'] ?? now()->format('d F Y');
        $hasTransactions = $context['has_transactions'] ?? false;
        $hasTransactionsText = $hasTransactions ? 'yes' : 'no';

        $unloggedText = $unloggedCount > 0
            ? "Hari ini kamu ada {$unloggedCount} transaksi yang belum dicatat. "
            : "";

        return <<<PROMPT
You are a notification copywriter for Fintrack, a personal finance app with gen-z friendly tone.

TASK: Write an evening reminder notification (19:00 WIB) to input daily transactions.

CONTEXT:
- Date: {$date}
- Has transactions today: {$hasTransactionsText}
- Unlogged count: {$unloggedCount}

TEMPLATE OPTIONS (choose one style):
- Title: "Daily wrap-up time 🌙"
  Body: "Sebelum rebahan, input transaksi hari ini ya. Biar besok hidup tanpa plot twist."

- Title: "Malam ini… jujur sama dompet 😌"
  Body: "Catat pengeluaran/pemasukan hari ini. Biar 'kok habis ya?' nggak kejadian lagi."

- Title: "1 menit sebelum tidur ⏳"
  Body: "Quick check: transaksi hari ini udah masuk semua belum? Fintrack mau bantu kamu waras finansial."

RULES:
- Language: Indonesian
- Tone: light, friendly, gen-z
- Max 110 characters for body
- Max 1 emoji in title
- {$unloggedText}If unlogged_count > 0, mention it naturally

Return JSON only:
{"title":"","body":""}
PROMPT;
    }

    /**
     * Budget Warning (general, >50%)
     */
    protected function buildBudgetWarningPrompt(array $context): string
    {
        $budgetName = $context['goal_name'] ?? $context['budget_name'] ?? 'Budget';
        $progress = $context['progress'] ?? $context['percent_used'] ?? 0;
        $spent = $context['spent'] ?? 0;
        $limit = $context['limit'] ?? $context['target_amount'] ?? 0;
        $remaining = $context['remaining'] ?? $context['remaining_amount'] ?? 0;

        return <<<PROMPT
You are a notification copywriter for Fintrack, a personal finance app with gen-z friendly tone.

TASK: Write a budget warning notification (similar to BUDGET_WARNING_50 template).

CONTEXT:
- Budget name: {$budgetName}
- Progress: {$progress}%
- Spent: {$spent}
- Limit: {$limit}
- Remaining: {$remaining}

Use the same template style as budget warning 50% (friendly-warning, tegas, or dengan saran aksi).

RULES:
- Language: Indonesian
- Tone: gen-z, friendly, calm, no shaming
- Max 120 characters for body
- Max 1 emoji in title

Return JSON only:
{"title":"","body":""}
PROMPT;
    }

    /**
     * Goal Progress 50%
     */
    protected function buildGoalProgress50Prompt(array $context): string
    {
        $goalName = $context['goal_name'] ?? 'Goal';
        $progress = $context['progress'] ?? 50;
        $current = $context['current'] ?? 0;
        $target = $context['target'] ?? $context['target_amount'] ?? 0;
        $remaining = $context['remaining'] ?? 0;

        return <<<PROMPT
You are a notification copywriter for Fintrack, a personal finance app with gen-z friendly tone.

TASK: Write a goal progress notification at 50% milestone.

CONTEXT:
- Goal name: {$goalName}
- Progress: {$progress}%
- Current: {$current}
- Target: {$target}
- Remaining: {$remaining}

TEMPLATE OPTIONS (choose one):
- Title: "Goal kamu udah 50% 🎯"
  Body: "Mantap! {$goalName} tinggal separuh lagi. Konsisten dikit, nanti selesai tanpa drama."

- Title: "Halfway glow up ✨"
  Body: "{$goalName} udah {$progress}%. Kamu bukan cuma niat, kamu bukti."

RULES:
- Language: Indonesian
- Tone: encouraging, friendly, gen-z
- Max 120 characters for body
- Max 1 emoji in title

Return JSON only:
{"title":"","body":""}
PROMPT;
    }

    /**
     * Goal Progress 80%
     */
    protected function buildGoalProgress80Prompt(array $context): string
    {
        $goalName = $context['goal_name'] ?? 'Goal';
        $progress = $context['progress'] ?? 80;
        $current = $context['current'] ?? 0;
        $target = $context['target'] ?? $context['target_amount'] ?? 0;
        $remaining = $context['remaining'] ?? 0;

        return <<<PROMPT
You are a notification copywriter for Fintrack, a personal finance app with gen-z friendly tone.

TASK: Write a goal progress notification at 80% milestone (tinggal dikit).

CONTEXT:
- Goal name: {$goalName}
- Progress: {$progress}%
- Current: {$current}
- Target: {$target}
- Remaining: {$remaining}

TEMPLATE OPTIONS (choose one):
- Title: "80% bro… tinggal gas tipis 🚀"
  Body: "{$goalName} udah {$progress}%. Jangan lengah, ini fase 'hampir jadi'."

- Title: "Tinggal dikit lagi! 🧨"
  Body: "{$goalName} udah 80%+. Satu dua langkah lagi, langsung finish."

RULES:
- Language: Indonesian
- Tone: encouraging, motivating, gen-z
- Max 120 characters for body
- Max 1 emoji in title

Return JSON only:
{"title":"","body":""}
PROMPT;
    }

    /**
     * Goal Progress 90%
     */
    protected function buildGoalProgress90Prompt(array $context): string
    {
        $goalName = $context['goal_name'] ?? 'Goal';
        $progress = $context['progress'] ?? 90;
        $current = $context['current'] ?? 0;
        $target = $context['target'] ?? $context['target_amount'] ?? 0;
        $remaining = $context['remaining'] ?? 0;

        return <<<PROMPT
You are a notification copywriter for Fintrack, a personal finance app with gen-z friendly tone.

TASK: Write a goal progress notification at 90% milestone (nyaris finish).

CONTEXT:
- Goal name: {$goalName}
- Progress: {$progress}%
- Current: {$current}
- Target: {$target}
- Remaining: {$remaining}

TEMPLATE OPTIONS (choose one):
- Title: "90%: ini udah depan mata 👀"
  Body: "{$goalName} udah {$progress}%. Jangan kena godaan 'checkout dulu' ya."

- Title: "Final boss tinggal 10% 🏁"
  Body: "Kamu udah sejauh ini. 10% terakhir biasanya paling licin—tetap fokus."

RULES:
- Language: Indonesian
- Tone: encouraging, warning about final stretch, gen-z
- Max 120 characters for body
- Max 1 emoji in title

Return JSON only:
{"title":"","body":""}
PROMPT;
    }

    /**
     * Goal Progress (general, auto-detect milestone)
     */
    protected function buildGoalProgressPrompt(array $context): string
    {
        $goalName = $context['goal_name'] ?? 'Goal';
        $progress = $context['progress'] ?? 0;
        $current = $context['current'] ?? 0;
        $target = $context['target'] ?? $context['target_amount'] ?? 0;
        $remaining = $context['remaining'] ?? 0;

        // Auto-select template based on progress
        if ($progress >= 90) {
            return $this->buildGoalProgress90Prompt($context);
        } elseif ($progress >= 80) {
            return $this->buildGoalProgress80Prompt($context);
        } elseif ($progress >= 50) {
            return $this->buildGoalProgress50Prompt($context);
        }

        return <<<PROMPT
You are a notification copywriter for Fintrack, a personal finance app with gen-z friendly tone.

TASK: Write a goal progress notification.

CONTEXT:
- Goal name: {$goalName}
- Progress: {$progress}%
- Current: {$current}
- Target: {$target}
- Remaining: {$remaining}

RULES:
- Language: Indonesian
- Tone: encouraging, friendly, gen-z
- Max 120 characters for body
- Max 1 emoji in title
- Motivate user to continue

Return JSON only:
{"title":"","body":""}
PROMPT;
    }

    /**
     * Goal Completed 100%
     */
    protected function buildGoalCompletedPrompt(array $context): string
    {
        $goalName = $context['goal_name'] ?? 'Goal';
        $current = $context['current'] ?? 0;
        $target = $context['target'] ?? $context['target_amount'] ?? 0;

        return <<<PROMPT
You are a notification copywriter for Fintrack, a personal finance app with gen-z friendly tone.

TASK: Write a celebration notification for a completed savings goal (100%).

CONTEXT:
- Goal name: {$goalName}
- Current: {$current}
- Target: {$target}

TEMPLATE OPTIONS (choose one):
- Title: "GOAL COMPLETED ✅🔥"
  Body: "{$goalName} tembus 100%! Kamu resmi menang lawan impulsif. Mau set goal baru?"

- Title: "Kamu berhasil. Beneran. 🏆"
  Body: "Target {$goalName} kelar! Saatnya selebrasi yang tetap waras: pilih reward kecil, bukan balas dendam belanja."

RULES:
- Language: Indonesian
- Tone: celebratory but smart, gen-z
- Avoid encouraging impulsive spending
- Max 120 characters for body
- Max 2 emojis in title

Return JSON only:
{"title":"","body":""}
PROMPT;
    }

    /**
     * Static fallback copy (ANTI GAGAL) - Using templates from ai_notification_template.txt
     */
    protected function fallback(string $event, array $context): array
    {
        return match ($event) {

            'BUDGET_WARNING_50' => [
                'title' => 'Budget kamu udah setengah jalan 🧾',
                'body' => sprintf(
                    "Kamu udah pakai %s%% dari %s. Santai, tapi mulai 'rem' dikit ya biar nggak kebablasan.",
                    $context['percent_used'] ?? $context['progress'] ?? 50,
                    $context['budget_name'] ?? 'Budget'
                ),
                'source' => 'fallback',
            ],

            'DAILY_REMINDER_MORNING' => [
                'title' => 'Pagi bestie, catat dulu ☕',
                'body' => 'Biar rapi sampai akhir bulan, isi transaksi hari ini ya. 30 detik doang, serius.',
                'source' => 'fallback',
            ],

            'DAILY_REMINDER_EVENING' => [
                'title' => 'Daily wrap-up time 🌙',
                'body' => 'Sebelum rebahan, input transaksi hari ini ya. Biar besok hidup tanpa plot twist.',
                'source' => 'fallback',
            ],

            'BUDGET_WARNING' => [
                'title' => 'Budget mulai panas 🔥',
                'body' => sprintf(
                    '%s%% udah kepake. Mode: hemat tipis-tipis biar akhir bulan nggak survival mode.',
                    $context['progress'] ?? $context['percent_used'] ?? 0
                ),
                'source' => 'fallback',
            ],

            'GOAL_PROGRESS_50' => [
                'title' => 'Goal kamu udah 50% 🎯',
                'body' => sprintf(
                    'Mantap! %s tinggal separuh lagi. Konsisten dikit, nanti selesai tanpa drama.',
                    $context['goal_name'] ?? 'Goal'
                ),
                'source' => 'fallback',
            ],

            'GOAL_PROGRESS_80' => [
                'title' => '80% bro… tinggal gas tipis 🚀',
                'body' => sprintf(
                    '%s udah %s%%. Jangan lengah, ini fase "hampir jadi".',
                    $context['goal_name'] ?? 'Goal',
                    $context['progress'] ?? 80
                ),
                'source' => 'fallback',
            ],

            'GOAL_PROGRESS_90' => [
                'title' => '90%: ini udah depan mata 👀',
                'body' => sprintf(
                    '%s udah %s%%. Jangan kena godaan "checkout dulu" ya.',
                    $context['goal_name'] ?? 'Goal',
                    $context['progress'] ?? 90
                ),
                'source' => 'fallback',
            ],

            'GOAL_PROGRESS' => [
                'title' => 'Halfway glow up ✨',
                'body' => sprintf(
                    '%s udah %s%%. Kamu bukan cuma niat, kamu bukti.',
                    $context['goal_name'] ?? 'Goal',
                    $context['progress'] ?? 0
                ),
                'source' => 'fallback',
            ],

            'GOAL_COMPLETED' => [
                'title' => 'GOAL COMPLETED ✅🔥',
                'body' => sprintf(
                    '%s tembus 100%%! Kamu resmi menang lawan impulsif. Mau set goal baru?',
                    $context['goal_name'] ?? 'Goal'
                ),
                'source' => 'fallback',
            ],

            default => [
                'title' => 'Fintrack Reminder',
                'body' => 'Ada update keuangan buat kamu.',
                'source' => 'fallback',
            ],
        };
    }
}
