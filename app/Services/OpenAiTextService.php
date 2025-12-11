<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class OpenAiTextService
{
    public function generateShortText(string $systemPrompt, string $userPrompt, int $maxTokens = 160): string
    {
        $apiKey = config('services.openai.key');
        $model  = config('services.openai.model', 'gpt-4o-mini');

        if (empty($apiKey)) {
            // Kalau belum set API key, mending fail jelas
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $endpoint = 'https://api.openai.com/v1/chat/completions';

        $response = Http::withToken($apiKey)
            ->timeout(config('services.openai.timeout', 15))
            ->post($endpoint, [
                'model'    => $model,
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role'    => 'user',
                        'content' => $userPrompt,
                    ],
                ],
                'max_tokens'   => $maxTokens,
                'temperature'  => 0.7,
            ]);

        if (! $response->successful()) {
            // Bisa kamu log kalau mau
            throw new RuntimeException(
                'OpenAI API error: ' . $response->status() . ' ' . $response->body()
            );
        }

        $data = $response->json();

        $text = data_get($data, 'choices.0.message.content');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('OpenAI API returned empty content.');
        }

        // biar aman, trim dan batas panjang
        $text = trim($text);
        return Str::limit($text, 500, ''); // maksimal 500 char
    }
}
