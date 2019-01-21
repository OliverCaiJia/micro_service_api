<?php

namespace App\Listeners\Oneloan\Partner\Batch;

use App\Constants\SpreadNidConstant;
use App\Events\Oneloan\Partner\UserSpreadBatchEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Cache\OxygenDaiCache;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Dongfang\Config\DongfangConfig;
use App\Services\Core\Oneloan\Dongfang\DongfangService;
use App\Services\Core\Oneloan\Oxygendai\OxygendaiService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 氧气贷延迟推送
 *
 * Class UserSpreadOxygendaiBatchListener
 * @package App\Listeners\V1
 */
class UserSpreadOxygendaiBatchListener extends AppListener
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
    public function handle(UserSpreadBatchEvent $event)
    {
        try {
            //推送数据
            $batchData = $event->data;
            //若当前limit = 0(未设置限额) 可推送; 若当前未到限额 可推送
            if ($batchData['total'] < $batchData['limit'] or 0 == $batchData['limit']) {
                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($batchData);
                //没有流水推送，状态为2推送，3延迟推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status'] || 3 == $spreadLogInfo['status']) {
                    $spread['id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    $this->pushData($batchData);
                } else {
                    //修改延迟表状态为成功
                    UserSpreadFactory::updateSpreadBatch($batchData['batch_id']);
                }
            }
        } catch (\Exception $exception) {
            SLogger::getStream()->error('氧气贷延迟推送失败-catch');
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
        if ($data['type_nid'] == SpreadNidConstant::SPREAD_OXYGENDAI_NID) {
            $data['age'] = Utils::getAge($data['birthday']);
            //使用批量接口
            $spreads = array($data);
            $res = OxygendaiService::spreadList($spreads);
            $data['status'] = 0;
            $data['message'] = '数据为空';
            $data['response_code'] = 0;
            if (isset($res['ret'])) {
                if ($res['ret'] == '0') {
                    if (isset($res['data']['isSuccess'])) {
                        if ($res['data']['isSuccess'] == 'T') {
                            $data['message'] = '操作成功';
                            $data['status'] = 1;
                            $data['response_code'] = 1;
                        } else {
                            $data['message'] = $res['data']['errMsg'];
                            $data['response_code'] = 2;
                        }

                    }

                } else {
                    if ($res['ret'] == '13002' || $res['ret'] == '13012') {
                        //删除tokenid
                        OxygenDaiCache::delCache(OxygenDaiCache::TOKENID);
                    }
                    $data['message'] = $res['msg'];
                }
            }

            $data['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);

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
