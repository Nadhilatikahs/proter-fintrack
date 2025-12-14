<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use App\Services\FintrackReminderNotificationService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class FintrackDailyTransactionRemindersCommand extends Command
{
    // parameter {waktu} = pagi / malam
    protected $signature   = 'fintrack:daily-transaction-reminders {waktu=pagi}';
    protected $description = 'Kirim reminder AI untuk input transaksi harian (pagi & malam).';

    public function __construct(
        protected FintrackReminderNotificationService $reminderService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $waktu = $this->argument('waktu'); // pagi | malam
        if (! in_array($waktu, ['pagi', 'malam'], true)) {
            $this->error('Parameter waktu harus "pagi" atau "malam".');
            return self::FAILURE;
        }

        $today = Carbon::today()->toDateString();

        $users = User::query()
            ->whereHas('transactions') // minimal pernah punya transaksi
            ->get();

        $this->info("Sending {$waktu} reminders to {$users->count()} users ...");

        foreach ($users as $user) {
            $hasTodayTransactions = Transaction::query()
                ->where('user_id', $user->id)
                ->whereDate('date', $today)
                ->exists();

            try {
                $message = $this->reminderService
                    ->buildDailyTransactionReminder($user, $waktu, $hasTodayTransactions);

                Notification::make()
                    ->title('Daily Transaction Reminder')
                    ->body($message)
                    ->icon('heroicon-o-calendar-days')
                    ->iconColor('info')
                    ->sendToDatabase($user);

                $this->info("Reminder {$waktu} sent to user {$user->id}");
            } catch (\Throwable $e) {
                $this->error("Failed user {$user->id}: " . $e->getMessage());
            }
        }

        $this->info("Daily transaction reminders ({$waktu}) sent.");
        return self::SUCCESS;
    }
}
