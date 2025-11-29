<?php

namespace App\Listeners;

use App\Events\ReminderCreated;
use App\Mail\ReminderNotificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendReminderEmail implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ReminderCreated $event): void
    {
        $reminder = $event->reminder;
        $user     = $reminder->user;

        // Kalau user tidak punya email, berhenti
        if (! $user || ! $user->email) {
            return;
        }

        // Kirim email
        Mail::to($user->email)->send(
            new ReminderNotificationMail($reminder)
        );

        // Optional: update kolom sent_at
        $reminder->forceFill([
            'sent_at' => now(),
        ])->save();
    }
}
