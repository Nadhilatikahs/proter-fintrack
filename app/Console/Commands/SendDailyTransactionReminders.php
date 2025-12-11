<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AiReminderService;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Console\Command;

class SendDailyTransactionReminders extends Command
{
    // Argumen time-label: pagi / malam (supaya pesan bisa beda)
    protected $signature = 'fintrack:daily-transaction-reminders {timeLabel=pagi}';

    protected $description = 'Kirim notifikasi pengingat untuk input transaksi harian (pagi & malam).';

    public function handle(AiReminderService $ai): int
    {
        $timeLabel = $this->argument('timeLabel'); // pagi / malam

        $users = User::all(); // kalau mau, filter hanya user yang punya transaksi dsb.

        foreach ($users as $user) {
            $message = $ai->dailyTransactionMessage($user, $timeLabel);

            FilamentNotification::make()
                ->title('Reminder input transaksi harian')
                ->body($message)
                ->icon('heroicon-o-clipboard-document-check')
                ->info()
                ->sendToDatabase($user);
        }

        $this->info("Daily transaction reminders ({$timeLabel}) sent.");

        return self::SUCCESS;
    }
}
