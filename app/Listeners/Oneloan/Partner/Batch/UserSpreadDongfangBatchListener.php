<?php

namespace App\Listeners\Oneloan\Partner\Batch;

use App\Constants\DongfangConstant;
use App\Constants\SpreadNidConstant;
use App\Events\Oneloan\Partner\UserSpreadBatchEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Dongfang\DongfangConfig\DongfangConfig;
use App\Services\Core\Oneloan\Dongfang\DongfangService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Listeners\Oneloan\Partner\Callback\DongfangCallback;

/**
 * 东方延迟推送
 *
 * Class UserSpreadDongfangBatchListener
 * @package App\Listeners\V1
 */
class UserSpreadDongfangBatchListener extends AppListener
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
                    //SLogger::getStream()->info('东方延迟', ['data' => $batchData]);
                    $this->pushData($batchData);
                } else
                {
                    //修改延迟表状态为成功
                    UserSpreadFactory::updateSpreadBatch($batchData['batch_id']);
                }
            }
        } catch (\Exception $exception) {
            SLogger::getStream()->error('东方金融延迟推送失败-catch');
            SLogger::getStream()->error($exception->getMessage());
        }
    }

    /**
     * 数据处理
     *
     * @param array $spread spread表里的信息
     */
    public function pushData($spread)
    {
        if ($spread['type_nid'] == SpreadNidConstant::SPREAD_DONGFANG_NID) {
            $spread['age'] = Utils::getAge($spread['birthday']);
            //城市拼音
            $cityInfo = UserSpreadFactory::checkSpreadBatchCity($spread);
            $spread['cityname'] = isset($cityInfo['city_pinyin']) ? $cityInfo['city_pinyin'] : '';

            //推送数据
            $spreadParams = DongfangConfig::getParams($spread);
            DongfangService::register($spreadParams,
                function($res) use ($spread) {
                    //处理结果
                    //是否是延迟推送流水  0不是，1是
                    $spread['batch_status'] = 0;
                    DongfangCallback::handleRes($res, $spread);

                    //更新spread
                    UserSpreadFactory::updateSpreadByMobile($spread['mobile']);
                    //更新发送状态
                    UserSpreadFactory::updateSpreadBatch($spread['batch_id']);
                }, function ($e){

                });
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
        $dongfangCitys = DongfangConstant::PUSH_CITY_PINYIN;

        if (array_key_exists($city, $dongfangCitys)) {
            return $dongfangCitys[$city];
        }

        return false;
    }

    /**
     * 通过后台进行配置
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

        return $citys['city_pinyin'];
    }


}
