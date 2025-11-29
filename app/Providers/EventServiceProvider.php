<?php

namespace App\Providers;

use App\Events\ReminderCreated;
use App\Listeners\SendReminderEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Event bawaan Laravel (verifikasi email)
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Event custom Fintrack: setiap Reminder baru dibuat => kirim email
        ReminderCreated::class => [
            SendReminderEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
