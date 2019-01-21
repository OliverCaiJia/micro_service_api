<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Models\Chain\AddIntegral\DoAddIntegralHandler;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;

/**
 * Class AddCreditListener
 * @package App\Listeners\V1
 * 加积分事件监听
 */
class AddIntegralListener extends AppListener
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
        // 责任链处理加积分
        $integralHandler = new DoAddIntegralHandler($event->data);
        $integralHandler->handleRequest();
    }

}
