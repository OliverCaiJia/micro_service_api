<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Events\V1\UserSpreadEvent;
use App\Events\V1\UserSpreadCountEvent;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Orm\UserSpread;
use App\Models\Orm\UserSpreadType;
use App\Services\Core\Data\Paipaidai\PaipaidaiService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Models\Chain\Creditcard;

class UserPaipaidaiListener extends AppListener
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
    public function handle(UserSpreadEvent $event)
    {
        $type_nid = UserSpreadFactory::SPREAD_PAIPAIDAI_NID;
        $type = UserSpreadType::where('type_nid', $type_nid)->where('status', 1)->first();

        if(!empty($type))
        {
            $event->data['type_id'] = $type ? $type->id : 0;
            $event->data['limit'] = $type ? $type->limit : 0;
            $event->data['total'] = $type ? $type->total : 0;

            // 插入用户数据
            UserSpreadFactory::createOrUpdateUserSpread($event->data);

            // 推广统计
            $spread = UserSpread::where('mobile', $event->data['mobile'])->first();
            $spread['type_id'] = $event->data['type_id'];
            event(new UserSpreadCountEvent($spread->toArray()));

            if(!UserSpreadFactory::checkIsSpread($event->data))
            {
                $this->pushData($event->data, $spread);
            }
        }
    }

    /**
     * 处理拍拍贷数据
     * @param $data
     * @return bool
     */
    public function pushData($data, $spread)
    {
        $typeNid = UserSpreadFactory::SPREAD_PAIPAIDAI_NID;//'';
        $limit = SpreadStrategy::getPushProductLimit($typeNid, $data['mobile']);
        if($limit)
        {
            return;
        }

        if ($data['type_id'] != 0)
        {
            // 若当前limit = 0(未设置限额) 可推送; 若当前未到限额 可推送
            if(($data['total'] < $data['limit']) or ($data['limit'] == 0))
            {
                // 创建流水
                $spread['type_id'] = $data['type_id'];
                $params['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
                // 推送service
                $res = PaipaidaiService::spread($spread);

                $params['message'] = '未知';
                $params['status'] = 0;
                $params['result'] = '未知';
                if (isset($res['Code'])) {
                    if (intval($res['Code']) == 1) {
                        $params['message'] = '操作成功';
                        $params['status'] = 1;
                    } else {
                        $params['message'] = $res['Msg'];
                        $params['status'] = 0;
                    }
                }

                $params['type_id'] = $data['type_id'];
                $params['mobile'] = $data['mobile'];
                $params['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
                //更新流水
                UserSpreadFactory::insertOrUpdateUserSpreadLog($params);

                // 更新推送次数等数据
                UserSpreadFactory::updateSpreadTypeTotal($typeNid, $params['status']);
            }
        }
    }
}
