<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Models\Chain\Creditcard;
use App\Models\Chain\Creditcard\Apply\DoApplyHandler;

class CardApplyListener extends AppListener
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
        // 责任链处理跳转统计逻辑
        $applyHandler = new DoApplyHandler($event->data);
        $applyHandler->handleRequest();
    }

}
