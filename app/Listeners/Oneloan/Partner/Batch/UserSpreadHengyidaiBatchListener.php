<?php

namespace App\Listeners\Oneloan\Partner\Batch;

use App\Constants\HengyidaiConstant;
use App\Constants\SpreadNidConstant;
use App\Events\Oneloan\Partner\UserSpreadBatchEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Hengchang\Hengyidai\HengyidaiConfig\HengyidaiConfig;
use App\Services\Core\Oneloan\Hengchang\Hengyidai\HengyidaiService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 恒昌线下恒易贷延迟推送
 *
 * Class UserSpreadHengyidaiBatchListener
 * @package App\Listeners\V1
 */
class UserSpreadHengyidaiBatchListener extends AppListener
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
            if ($batchData['total'] < $batchData['limit'] or 0 == $batchData['limit'])
            {
                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($batchData);
                //没有流水推送，状态为2推送，3延迟推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status'] || 3 == $spreadLogInfo['status'])
                {
                    $spread['id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    $this->pushData($batchData);
                } else {
                    //修改延迟表状态为成功
                    UserSpreadFactory::updateSpreadBatch($batchData['batch_id']);
                }
            }
        } catch (\Exception $exception) {
            SLogger::getStream()->error('恒昌金融延迟推送失败-catch');
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
        if ($data['type_nid'] == SpreadNidConstant::SPREAD_HENGYIDAI_NID) {
            //年龄
            $data['age'] = Utils::getAge($data['birthday']);
            //城市信息
            $cityInfo = UserSpreadFactory::checkSpreadCity($data);
            $data['cityCode'] = isset($cityInfo['city_code']) ? $cityInfo['city_code'] : '';

            //调用恒昌service
            $res = HengyidaiService::register($data);
            $data['status'] = 0;
            $data['message'] = '数据为空';
            $data['response_code'] = 0;

            $data['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
            if (isset($res['ResponseCode'])) {
                $data['message'] = HengyidaiConfig::getMessage($res['ResponseCode']);
                if ($res['ResponseCode'] == 0) {
                    $data['status'] = 1;
                    $data['response_code'] = 1;
                } else {
                    $data['status'] = 0;
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

    /**
     * 检查城市
     *
     * @param array $data
     * @return bool
     */
    private function checkCity($data = [])
    {
        $city = isset($data['city']) ? $data['city'] : '';
        $hengyidaiCitys = HengyidaiConstant::PUSH_CITYS_CODE;

        if (array_key_exists($city, $hengyidaiCitys)) {
            return $hengyidaiCitys[$city];
        }

        return false;
    }

    /**
     * 后台配置城市
     *
     * @param array $data
     * @return bool
     */
    private function reCheckCity($data = [])
    {
        //城市信息
        $citys = UserSpreadFactory::fetchUserSpreadAreasByTypeIdAndCityName($data);
        //有城市限制
        if (empty($citys)) {
            return false;
        }

        return $citys['city_code'];
    }
}
