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
        $this->apiKey   = config('services.openai.key');
        $this->model    = config('ai.model');
    }

    /**
     * Generate notification copy via AI
     */
    public function generate(string $event, array $context): array
    {
        try {
            $prompt = $this->buildPrompt($event, $context);

            $http = Http::withToken($this->apiKey)
                ->timeout(config('ai.timeout', 15));

            // Disable SSL verification in local (Windows)
            if (app()->environment('local')) {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->post($this->endpoint, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional notification copywriter.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

            if (! $response->successful()) {
                Log::error('AI API response error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new Exception('AI request failed');
            }

            $content = $response->json('choices.0.message.content');
            $parsed = json_decode($content, true);

            if (! isset($parsed['title'], $parsed['body'])) {
                throw new Exception('Invalid AI response format');
            }

            return [
                'title' => trim($parsed['title']),
                'body'  => trim($parsed['body']),
                'source'=> 'ai',
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
     * Prompt builder
     */
    protected function buildPrompt(string $event, array $context): string
    {
        return match ($event) {

            'BUDGET_WARNING_50' => <<<PROMPT
You are a notification copywriter for Fintrack.

Write a budget alert when usage reaches 50%.

Context:
- Budget name: {$context['budget_name']}
- Usage: {$context['percent_used']}%

Rules:
- Indonesian
- Friendly, gen-z
- Max 120 chars

Return JSON:
{"title":"","body":""}
PROMPT,

            'DAILY_REMINDER_MORNING' => <<<PROMPT
Write a friendly morning reminder to input transactions.

Rules:
- Indonesian
- Max 110 chars

Return JSON.
PROMPT,

            'GOAL_COMPLETED' => <<<PROMPT
Write a celebration message for completed saving goal "{$context['goal_name']}".

Rules:
- Indonesian
- Avoid impulsive spending
- Max 120 chars

Return JSON.
PROMPT,

            default => throw new Exception("Unknown event: {$event}")
        };
    }

    /**
     * Static fallback copy
     */
    protected function fallback(string $event, array $context): array
    {
        return match ($event) {

            'BUDGET_WARNING_50' => [
                'title' => 'Budget setengah jalan 🧾',
                'body'  => "{$context['budget_name']} sudah 50% terpakai. Yuk lebih aware biar aman.",
                'source'=> 'fallback',
            ],

            'DAILY_REMINDER_MORNING' => [
                'title' => 'Catat transaksi hari ini',
                'body'  => 'Biar rapi sampai akhir bulan, jangan lupa input transaksi ya.',
                'source'=> 'fallback',
            ],

            'GOAL_COMPLETED' => [
                'title' => 'Goal tercapai 🎯',
                'body'  => "{$context['goal_name']} berhasil 100%! Mantap, lanjut target berikutnya.",
                'source'=> 'fallback',
            ],

            default => [
                'title' => 'Fintrack Reminder',
                'body'  => 'Ada update keuangan buat kamu.',
                'source'=> 'fallback',
            ],
        };
    }
}
