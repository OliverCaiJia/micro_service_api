<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Events\V1\UserSpreadCountEvent;
use App\Models\Factory\UserSpreadFactory;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Models\Factory\DeliveryFactory;

/** 推广统计事件监听
 * Class AddCreditListener
 * @package App\Listeners\V1
 *
 */
class UserSpreadCountListener extends AppListener
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
    public function handle(UserSpreadCountEvent $event)
    {
        $deliveryId = DeliveryFactory::fetchDeliveryId($event->data['user_id']);
        //获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);
        $event->data['channel_id'] = $deliveryArr['id'];
        $event->data['channel_title'] = $deliveryArr['title'];
        $event->data['channel_nid'] = $deliveryArr['nid'];
        $re = UserSpreadFactory::insertDataUserSpreadLog($event->data);
    }

}
