<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;   
use Illuminate\Support\Facades\Log;

class SendWelcomeMailListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        // Log::info('Log before mail send');
        // Log::info('$event->user->email' .$event->user->email);
        // Log::info('$event->user' .$event->user);

        Mail::to($event->user->email)->send(new WelcomeMail($event->user));
        // Log::info('Mail sent to user from event : '.$event->user->email);

    }
}
