<?php

namespace App\Mail;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Reminder $reminder;

    /**
     * Create a new message instance.
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $user = $this->reminder->user;

        return $this
            ->subject('Fintrack Reminder: ' . ($this->reminder->title ?? 'Update kondisi keuangan'))
            ->markdown('emails.reminders.notification', [
                'user'     => $user,
                'reminder' => $this->reminder,
                'data'     => $this->reminder->data ?? [],
            ]);
    }
}
