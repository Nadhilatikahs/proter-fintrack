<?php

namespace App\Listeners;

use App\Events\ReminderCreated;
use App\Mail\ReminderNotificationMail;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Actions\Action as NotificationAction;
use Illuminate\Support\Str;

class SendReminderEmail implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ReminderCreated $event): void
    {
        $reminder = $event->reminder;
        $user     = $reminder->user;

        if (! $user || ! $user->email) {
            return;
        }

        // 1) Kirim email (mailer = log di dev)
        Mail::to($user->email)->send(
            new ReminderNotificationMail($reminder)
        );

        // Tandai sudah dikirim email
        $reminder->forceFill([
            'sent_at' => now(),
        ])->save();

        // 2) Kirim NOTIFIKASI ke bell icon (database notifications)
        //    body dipotong biar pendek.
        $title = $reminder->title ?? 'Fintrack Reminder';
        $body  = Str::limit($reminder->message, 150);

        // Cara 1: fluent API
        Notification::make()
            ->title($title)
            ->body($body)
            ->success() // boleh diubah sesuai type
            ->actions([
                NotificationAction::make('view')
                    ->label('Lihat detail')
                    ->button()
                    // langsung buka halaman Notifikasi Fintrack (Filament page kita)
                    ->url(url('/admin/notifications'))
                    ->openUrlInNewTab(false),
            ])
            ->toDatabase()              // jadikan database notification
            ->sendToDatabase($user);    // kirim ke user ini
    }
}