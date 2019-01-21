<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;

class UserLoginListener extends AppListener
{

    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AppEvent  $event
     * @return void
     */
    public function handle(AppEvent $event)
    {
        //
    }

}
