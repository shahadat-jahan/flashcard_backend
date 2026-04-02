<?php

namespace App\Listeners;

use Carbon\Carbon;
use hisorange\BrowserDetect\Facade as Browser;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $loginInfo = [
            'login_at' => Carbon::now()->toDateTimeString(),
            'ip' => request()->getClientIp(),
            'device' => Browser::deviceType(),   // Fetching device type
            'browser' => Browser::browserName(),   // Fetching browser name
            'platform' => Browser::platformName(),  // Fetching platform name
        ];

        activity()
            ->causedBy($event->user->id)
            ->performedOn($event->user)   // Log activity on the logged-in user model
            ->withProperties($loginInfo)  // Attach login details to the activity log
            ->log('login');
    }
}
