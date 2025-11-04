<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateLastLogin
{
    /**
     * Create the event listener.
     */
    public function handle(Login $event)
    {
        $event->user->last_login_at = Carbon::now();
        $event->user->save();
    }
}
