<?php

namespace App\Listeners\Oneloan\Partner\Batch;


use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadBatchEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Listeners\AppListener;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Youxin\Miaodai\Config\MiaodaiConfig;
use App\Services\Core\Oneloan\Youxin\Miaodai\MiaodaiService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;

class UserSpreadMiaodaiBatchListener extends AppListener
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
        try {
            //推送数据
            $batchData = $event->data;
            //若当前limit = 0(未设置限额) 可推送; 若当前未到限额 可推送
            if ($batchData['total'] < $batchData['limit'] or 0 == $batchData['limit']) {
                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($batchData);
                //没有流水推送，状态为2推送,3延迟推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status'] || 3 == $spreadLogInfo['status'])
                {
                    $batchData['id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    $this->pushData($batchData);
                } else
                {
                    //修改延迟表状态为成功
                    UserSpreadFactory::updateSpreadBatch($batchData['batch_id']);
                }
            }
        } catch (\Exception $exception) {
            SLogger::getStream()->error('秒贷延迟推送失败-catch');
            SLogger::getStream()->error($exception->getMessage());
        }
    }

    /**
     * 数据处理
     *
     * @param array $data spread表里的信息
     * @return bool
     */
    public function pushData($data)
    {
        if ($data['type_nid'] == SpreadNidConstant::SPREAD_MIAODAI_NID) {
            $data['age'] = Utils::getAge($data['birthday']);
            //数据处理
            $reqDatas = MiaodaiConfig::formatDatas($data);
            //推送数据
            $res = MiaodaiService::spread($reqDatas);

            $data['status'] = 0;
            $data['message'] = '数据为空';
            $data['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
            $data['response_code'] = 0;
            if(isset($res['code']) && $res['code'] == 0 ) //成功
            {
                if (isset($res['data']['validResult']) && $res['data']['validResult'] == 'success') {
                    $data['status'] = 1;
                    $data['message'] = '成功';
                    $data['response_code'] = 1;
                } else {
                    $data['status'] = 0;
                    $data['message'] = '失败';
                    $data['response_code'] = 2;
                }
            }

            // 更新spreadLog
            //是否是延迟推送【0立即推送，1延迟推送】
            $data['batch_status'] = 1;
            if (!UserSpreadFactory::checkIsSpread($data)) {
                $data['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($data);
            } else {
                // 更新分发数据状态
                UserSpreadFactory::insertOrUpdateUserSpreadLog($data);
            }

            //更新spreadType
            SpreadStrategy::updateSpreadCounts($data);

            //更新spread
            UserSpreadFactory::updateSpreadByMobile($data['mobile']);

            //更新发送状态
            UserSpreadFactory::updateSpreadBatch($data['batch_id']);
        }
    }
}