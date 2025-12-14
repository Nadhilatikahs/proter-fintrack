<?php

namespace App\Console\Commands;

use App\Models\Reminder;
use App\Models\User;
use App\Services\NotificationAiService;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    protected $signature = 'test:notification
                            {--user= : User ID or email}
                            {--type=budget_warning : Notification type (budget_warning, goal_progress, daily_reminder, all)}
                            {--ai : Use AI to generate notification content (requires OpenAI API key)}';

    protected $description = 'Create a test notification/reminder. Use --ai to generate content via OpenAI API.';

    public function handle()
    {
        $userInput = $this->option('user');
        $type = $this->option('type');
        $useAi = $this->option('ai');

        // Find user
        if ($userInput) {
            $user = is_numeric($userInput)
                ? User::find($userInput)
                : User::where('email', $userInput)->first();
        } else {
            $user = User::first();
        }

        if (!$user) {
            $this->error('User not found!');
            return 1;
        }

        if ($useAi) {
            return $this->handleAiNotification($user, $type);
        }

        // Create test notifications based on type (static templates)
        $notifications = $this->getTestNotifications($type);

        foreach ($notifications as $notification) {
            Reminder::create([
                'user_id' => $user->id,
                'type' => $notification['type'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'is_read' => false,
                'sent_at' => now(),
            ]);
        }

        $this->info("✅ Created " . count($notifications) . " test notification(s) for user: {$user->email}");
        $this->info("📱 Check the notification bell in the admin panel!");

        return 0;
    }

    /**
     * Handle AI-generated notification
     */
    protected function handleAiNotification(User $user, string $type): int
    {
        $aiService = app(NotificationAiService::class);

        $this->info("🤖 Calling OpenAI API to generate notification...");
        $this->newLine();

        // Map type to event
        $events = $this->getAiEvents($type);

        foreach ($events as $eventData) {
            $event = $eventData['event'];
            $context = $eventData['context'];
            $reminderType = $eventData['reminder_type'];

            $this->info("📤 Sending request for event: {$event}");

            $result = $aiService->generate($event, $context);

            $this->info("📥 Response received:");
            $this->table(
                ['Field', 'Value'],
                [
                    ['Source', $result['source'] ?? 'unknown'],
                    ['Title', $result['title'] ?? '-'],
                    ['Body', $result['body'] ?? '-'],
                ]
            );

            // Save to database
            Reminder::create([
                'user_id' => $user->id,
                'type' => $reminderType,
                'title' => $result['title'],
                'message' => $result['body'],
                'is_read' => false,
                'sent_at' => now(),
                'data' => [
                    'source' => $result['source'],
                    'event' => $event,
                    'context' => $context,
                ],
            ]);

            $this->info("✅ Notification saved to database!");
            $this->newLine();
        }

        $this->info("🎉 Done! Check the notification bell in the admin panel.");

        return 0;
    }

    /**
     * Get AI events based on type
     */
    protected function getAiEvents(string $type): array
    {
        $events = [
            'budget_warning' => [
                [
                    'event' => 'BUDGET_WARNING_50',
                    'reminder_type' => 'budget_warning',
                    'context' => [
                        'budget_name' => 'Budget Makan',
                        'percent_used' => 50,
                        'spent' => 500000,
                        'limit' => 1000000,
                        'remaining' => 500000,
                    ],
                ],
            ],
            'budget_over_limit' => [
                [
                    'event' => 'BUDGET_WARNING',
                    'reminder_type' => 'budget_over_limit',
                    'context' => [
                        'budget_name' => 'Budget Shopping',
                        'progress' => 85,
                        'spent' => 850000,
                        'limit' => 1000000,
                        'remaining' => 150000,
                    ],
                ],
            ],
            'goal_progress' => [
                [
                    'event' => 'GOAL_PROGRESS_80',
                    'reminder_type' => 'goal_progress',
                    'context' => [
                        'goal_name' => 'Tabungan Liburan',
                        'progress' => 80,
                        'current' => 8000000,
                        'target' => 10000000,
                        'remaining' => 2000000,
                    ],
                ],
            ],
            'goal_completed' => [
                [
                    'event' => 'GOAL_COMPLETED',
                    'reminder_type' => 'goal_completed',
                    'context' => [
                        'goal_name' => 'Dana Darurat',
                        'current' => 5000000,
                        'target' => 5000000,
                    ],
                ],
            ],
            'daily_reminder' => [
                [
                    'event' => 'DAILY_REMINDER_EVENING',
                    'reminder_type' => 'daily_reminder',
                    'context' => [
                        'date' => now()->format('d F Y'),
                        'has_transactions' => false,
                        'unlogged_count' => 3,
                    ],
                ],
            ],
            'all' => [
                [
                    'event' => 'BUDGET_WARNING_50',
                    'reminder_type' => 'budget_warning',
                    'context' => [
                        'budget_name' => 'Budget Makan',
                        'percent_used' => 50,
                        'spent' => 500000,
                        'limit' => 1000000,
                        'remaining' => 500000,
                    ],
                ],
                [
                    'event' => 'GOAL_PROGRESS_80',
                    'reminder_type' => 'goal_progress',
                    'context' => [
                        'goal_name' => 'Tabungan Liburan',
                        'progress' => 80,
                        'current' => 8000000,
                        'target' => 10000000,
                        'remaining' => 2000000,
                    ],
                ],
                [
                    'event' => 'DAILY_REMINDER_EVENING',
                    'reminder_type' => 'daily_reminder',
                    'context' => [
                        'date' => now()->format('d F Y'),
                        'has_transactions' => false,
                        'unlogged_count' => 2,
                    ],
                ],
            ],
        ];

        return $events[$type] ?? $events['budget_warning'];
    }

    /**
     * Get static test notifications (fallback/without AI)
     */
    protected function getTestNotifications(string $type): array
    {
        $templates = [
            'budget_warning' => [
                [
                    'type' => 'budget_warning',
                    'title' => 'Budget kamu udah setengah jalan 🧾',
                    'message' => 'Kamu udah pakai 50% dari Budget Makan. Santai, tapi mulai "rem" dikit ya biar nggak kebablasan.',
                ],
            ],
            'budget_over_limit' => [
                [
                    'type' => 'budget_over_limit',
                    'title' => 'Budget mulai panas 🔥',
                    'message' => '75% udah kepake. Mode: hemat tipis-tipis biar akhir bulan nggak survival mode.',
                ],
            ],
            'goal_progress' => [
                [
                    'type' => 'goal_progress',
                    'title' => 'Goal kamu udah 50% 🎯',
                    'message' => 'Mantap! Tabungan Liburan tinggal separuh lagi. Konsisten dikit, nanti selesai tanpa drama.',
                ],
            ],
            'goal_completed' => [
                [
                    'type' => 'goal_completed',
                    'title' => 'GOAL COMPLETED ✅🔥',
                    'message' => 'Tabungan Liburan tembus 100%! Kamu resmi menang lawan impulsif. Mau set goal baru?',
                ],
            ],
            'daily_reminder' => [
                [
                    'type' => 'daily_reminder',
                    'title' => 'Pagi bestie, catat dulu ☕',
                    'message' => 'Biar rapi sampai akhir bulan, isi transaksi hari ini ya. 30 detik doang, serius.',
                ],
            ],
            'all' => [
                [
                    'type' => 'budget_warning',
                    'title' => 'Budget kamu udah setengah jalan 🧾',
                    'message' => 'Kamu udah pakai 50% dari Budget Makan.',
                ],
                [
                    'type' => 'goal_progress',
                    'title' => 'Goal kamu udah 80% 🚀',
                    'message' => 'Tabungan Liburan udah 80%. Jangan lengah!',
                ],
                [
                    'type' => 'daily_reminder',
                    'title' => 'Daily wrap-up time 🌙',
                    'message' => 'Sebelum rebahan, input transaksi hari ini ya.',
                ],
            ],
        ];

        return $templates[$type] ?? $templates['budget_warning'];
    }
}
