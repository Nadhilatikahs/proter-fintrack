<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Budget & goal reminder – misal tiap jam 08:00
        $schedule->command('fintrack:budget-goal-reminders')
            ->dailyAt('08:00')
            ->timezone('Asia/Jakarta');

        // Reminder transaksi harian pagi
        $schedule->command('fintrack:daily-transaction-reminders pagi')
            ->dailyAt('09:00')
            ->timezone('Asia/Jakarta');

        // Reminder transaksi harian malam
        $schedule->command('fintrack:daily-transaction-reminders malam')
            ->dailyAt('19:00')
            ->timezone('Asia/Jakarta');
    }

    // ...
}
