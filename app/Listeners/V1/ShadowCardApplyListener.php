<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Models\Chain\Creditcard\ShadowApply\DoApplyHandler;

class ShadowCardApplyListener extends AppListener
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
        // 责任链处理马甲包跳转统计逻辑
        $applyHandler = new DoApplyHandler($event->data);
        return $applyHandler->handleRequest();
    }

}
