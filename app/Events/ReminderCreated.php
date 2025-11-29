<?php

namespace App\Events;

use App\Models\Reminder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReminderCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reminder $reminder;

    /**
     * Create a new event instance.
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }
}
