<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\UserFactory;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Helpers\Logger\SLogger;
use Illuminate\Support\Facades\DB;

/**
 * 渠道统计事件监听
 * Class UserRegCountListener
 * @package App\Listeners\V1
 */
class UserRegCountListener extends AppListener
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
     * @param  AppEvent $event
     * @return void
     */
    public function handle(AppEvent $event)
    {
        $count = $event->count;
        DB::beginTransaction();
        try
        {
            // 渠道ID
            $channelId = DeliveryFactory::fetchChannelId($count['channel_fr']);
            //渠道注册统计日志是否重复
            $data['user_id'] = $count['userId'];
            $data['channel_nid'] = $count['channel_fr'];
            $registerDeliveryLog = DeliveryFactory::fetchRegisterDeliveryLog($data);
            if (empty($registerDeliveryLog))
            {
                // 渠道统计日志
                $channelLog = DeliveryFactory::insertDeliverylLog($count);
                // 渠道统计汇总
                $channelCount = DeliveryFactory::updateDeliveryRegisterCount($count['channel_fr']);
            }

            //注册时添加用户渠道关系
            $channelUser = DeliveryFactory::createUserDelivery($count['userId'], $channelId);
            //注册时添加用户身份信息
            $userAgent = UserFactory::createUserAgent($count);
            if (!$channelLog || !$channelCount || !$channelUser)
            {
                DB::rollback();

                SLogger::getStream()->error('注册渠道统计失败-try');
            }
            else
            {
                DB::commit();
            }
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            SLogger::getStream()->error('注册渠道统计失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return false;
    }

}
