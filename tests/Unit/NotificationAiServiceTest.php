<?php

namespace Tests\Unit;

use App\Services\NotificationAiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Exception;

class NotificationAiServiceTest extends TestCase
{
    protected NotificationAiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationAiService();
    }

    /** @test */
    public function it_generates_budget_warning_50_notification_successfully(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Budget kamu udah setengah jalan 🧾',
                                'body' => 'Kamu udah pakai 50% dari Budget Makan. Santai, tapi mulai "rem" dikit ya biar nggak kebablasan.',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generate('BUDGET_WARNING_50', [
            'budget_name' => 'Budget Makan',
            'percent_used' => 50,
            'spent' => 500000,
            'limit' => 1000000,
            'remaining' => 500000,
        ]);

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('body', $result);
        $this->assertArrayHasKey('source', $result);
        $this->assertEquals('ai', $result['source']);
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['body']);
    }

    /** @test */
    public function it_falls_back_to_template_when_ai_request_fails(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        Log::shouldReceive('warning')->once();

        $result = $this->service->generate('BUDGET_WARNING_50', [
            'budget_name' => 'Budget Makan',
            'percent_used' => 50,
        ]);

        $this->assertEquals('fallback', $result['source']);
        $this->assertStringContainsString('Budget kamu udah setengah jalan', $result['title']);
        $this->assertStringContainsString('Budget Makan', $result['body']);
    }

    /** @test */
    public function it_falls_back_when_ai_returns_invalid_json(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Invalid JSON response',
                        ],
                    ],
                ],
            ], 200),
        ]);

        Log::shouldReceive('warning')->once();

        $result = $this->service->generate('DAILY_REMINDER_MORNING', []);

        $this->assertEquals('fallback', $result['source']);
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['body']);
    }

    /** @test */
    public function it_falls_back_when_ai_returns_missing_fields(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Only title',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        Log::shouldReceive('warning')->once();

        $result = $this->service->generate('DAILY_REMINDER_MORNING', []);

        $this->assertEquals('fallback', $result['source']);
    }

    /** @test */
    public function it_generates_morning_reminder_successfully(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Pagi bestie, catat dulu ☕',
                                'body' => 'Biar rapi sampai akhir bulan, isi transaksi hari ini ya. 30 detik doang, serius.',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generate('DAILY_REMINDER_MORNING', [
            'date' => '15 Januari 2024',
            'has_transactions' => false,
            'unlogged_count' => 0,
        ]);

        $this->assertEquals('ai', $result['source']);
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['body']);
    }

    /** @test */
    public function it_generates_evening_reminder_successfully(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Daily wrap-up time 🌙',
                                'body' => 'Sebelum rebahan, input transaksi hari ini ya. Biar besok hidup tanpa plot twist.',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generate('DAILY_REMINDER_EVENING', [
            'date' => '15 Januari 2024',
            'has_transactions' => true,
            'unlogged_count' => 2,
        ]);

        $this->assertEquals('ai', $result['source']);
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['body']);
    }

    /** @test */
    public function it_generates_goal_progress_50_successfully(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'Goal kamu udah 50% 🎯',
                                'body' => 'Mantap! Tabungan Liburan tinggal separuh lagi. Konsisten dikit, nanti selesai tanpa drama.',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generate('GOAL_PROGRESS_50', [
            'goal_name' => 'Tabungan Liburan',
            'progress' => 50,
            'current' => 5000000,
            'target' => 10000000,
            'remaining' => 5000000,
        ]);

        $this->assertEquals('ai', $result['source']);
        $this->assertNotEmpty($result['title']);
        $this->assertNotEmpty($result['body']);
    }

    /** @test */
    public function it_generates_goal_progress_80_successfully(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => '80% bro… tinggal gas tipis 🚀',
                                'body' => 'Tabungan Liburan udah 80%. Jangan lengah, ini fase "hampir jadi".',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generate('GOAL_PROGRESS_80', [
            'goal_name' => 'Tabungan Liburan',
            'progress' => 80,
        ]);

        $this->assertEquals('ai', $result['source']);
    }

    /** @test */
    public function it_generates_goal_progress_90_successfully(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => '90%: ini udah depan mata 👀',
                                'body' => 'Tabungan Liburan udah 90%. Jangan kena godaan "checkout dulu" ya.',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generate('GOAL_PROGRESS_90', [
            'goal_name' => 'Tabungan Liburan',
            'progress' => 90,
        ]);

        $this->assertEquals('ai', $result['source']);
    }

    /** @test */
    public function it_generates_goal_completed_successfully(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => 'GOAL COMPLETED ✅🔥',
                                'body' => 'Tabungan Liburan tembus 100%! Kamu resmi menang lawan impulsif. Mau set goal baru?',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generate('GOAL_COMPLETED', [
            'goal_name' => 'Tabungan Liburan',
            'current' => 10000000,
            'target' => 10000000,
        ]);

        $this->assertEquals('ai', $result['source']);
        $this->assertStringContainsString('100%', $result['body']);
    }

    /** @test */
    public function it_uses_fallback_for_unknown_event_when_exception_caught(): void
    {
        // The exception is thrown in buildPrompt, but caught in generate method
        // So we test the fallback behavior when exception is caught
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        Log::shouldReceive('warning')->once();

        $result = $this->service->generate('UNKNOWN_EVENT', []);

        // Should fallback to default message when exception is caught
        $this->assertEquals('fallback', $result['source']);
        $this->assertEquals('Fintrack Reminder', $result['title']);
    }

    /** @test */
    public function it_handles_network_timeout_gracefully(): void
    {
        Http::fake([
            '*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
            },
        ]);

        Log::shouldReceive('warning')->once();

        $result = $this->service->generate('DAILY_REMINDER_MORNING', []);

        $this->assertEquals('fallback', $result['source']);
        $this->assertNotEmpty($result['title']);
    }

    /** @test */
    public function it_trims_whitespace_from_ai_response(): void
    {
        Http::fake([
            '*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'title' => '  Budget Alert  ',
                                'body' => '  Some message with spaces  ',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->generate('BUDGET_WARNING', [
            'budget_name' => 'Test',
            'progress' => 60,
        ]);

        $this->assertEquals('Budget Alert', $result['title']);
        $this->assertEquals('Some message with spaces', $result['body']);
    }
}

