<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * Di sini kita daftarkan semua jadwal cron/scheduler,
     * termasuk proses Reminder AI Fintrack.
     */
    protected function schedule(Schedule $schedule): void
    {
        /**
         * Contoh bawaan Laravel (opsional, boleh dihapus):
         *
         * $schedule->command('inspire')->hourly();
         */

        /**
         * Jadwal untuk fitur Reminder Fintrack.
         *
         * Command ini akan menjalankan proses:
         * - cek budget (warning / over-limit)
         * - cek financial goal yang mendekati target date
         * - membuat record di tabel reminders (dengan pesan dari AI)
         *
         * Pastikan file app\Console\Commands\ProcessFinancialReminders.php
         * punya signature:
         *   protected $signature = 'fintrack:process-reminders';
         */

        // Versi: jalan SETIAP JAM
        $schedule->command('fintrack:process-reminders')->hourly();

        // Kalau lebih suka hanya sekali sehari jam 20.00, komentari yang atas
        // dan pakai ini (pilih salah satu):
        // $schedule->command('fintrack:process-reminders')->dailyAt('20:00');
    }

    /**
     * Register the commands for the application.
     *
     * Di sini Laravel akan load semua command kustom di folder
     * app/Console/Commands secara otomatis.
     */
    protected function commands(): void
    {
        // Load semua command kustom (termasuk ProcessFinancialReminders)
        $this->load(__DIR__ . '/Commands');

        // Route console tambahan (biasanya kosong, tapi biarkan saja)
        require base_path('routes/console.php');
    }
}
