<?php

namespace App\Listeners\Oneloan\Partner\Batch;

use App\Constants\SpreadNidConstant;
use App\Events\Oneloan\Partner\UserSpreadBatchEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Cache\CommonCache;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Niwodai\Miaola\Config\MiaolaConfig;
use App\Services\Core\Oneloan\Niwodai\Miaola\MiaolaService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 你我贷-秒啦延迟推送
 *
 * Class UserSpreadNiwodaiBatchListener
 * @package App\Listeners\V1
 */
class UserSpreadMiaolaBatchListener extends AppListener
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
            //SLogger::getStream()->info('sss', ['data' => $event->data]);
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
            SLogger::getStream()->error('你我贷-秒啦延迟推送失败-catch');
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
        if ($data['type_nid'] == SpreadNidConstant::SPREAD_MIAOLA_NID) {
            $data['age'] = Utils::getAge($data['birthday']);
            //城市拼音
            $cityInfo = UserSpreadFactory::checkSpreadBatchCity($data);
            $data['cityname'] = isset($cityInfo['city_name']) ? $cityInfo['city_name'] : '';

            //推送数据
            $spreadParams = MiaolaConfig::getParams($data);
            $res = MiaolaService::apply($spreadParams);
            //我们好像所有的都是OPEN55020这个错误码，我就用这个了，到时候再次请求一次，弥补一下验签错误的情况
            if (isset($res['success']) && $res['success'] == 0 && $res['errCode'] == 'OPEN55020') {
                //删除cache缓存，重新获取
                CommonCache::delCache(CommonCache::NIWODAI_TOKEN);
                //重新请求接口
                $res = MiaolaService::apply($spreadParams);
            }
            $data['status'] = 0;
            $data['message'] = '数据为空';
            $data['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
            $data['response_code'] = 0;
            if (isset($res['success']) && $res['success'] == 1) {
                $data['status'] = 1;
                $data['message'] = '成功';
                $data['response_code'] = 1;
            } else {
                $data['status'] = 0;
                $data['message'] = isset($res['errMsg']) ? $res['errMsg'] : '失败';;
                $data['response_code'] = 2;
            }

            //SLogger::getStream()->info('log', ['log' => $data]);
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
