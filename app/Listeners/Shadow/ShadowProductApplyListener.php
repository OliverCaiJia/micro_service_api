<?php

namespace  App\Listeners\Shadow;

use App\Events\AppEvent;
use App\Models\Factory\DeliveryFactory;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;

class ShadowProductApplyListener extends AppListener
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
        // 跳转统计
        $data = $event->data;
        // 创建马甲包流水
        $res = DeliveryFactory::createShadowProductApplyLog($data);

        return $res;
    }

}
