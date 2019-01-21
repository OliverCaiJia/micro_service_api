<?php

namespace App\Listeners\Oneloan\Partner;

use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Models\Factory\UserSpreadFactory;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Models\Factory\DeliveryFactory;
use Illuminate\Support\Facades\Log;

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
     * @param UserSpreadCountEvent $event
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
