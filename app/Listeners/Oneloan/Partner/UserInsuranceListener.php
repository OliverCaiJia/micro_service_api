<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\HeiniuConstant;
use App\Constants\SpreadConstant;
use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserInsuranceEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Heiniu\HeiniuService;
use App\Services\Core\Tools\JuHe\Phone\JuhePhoneService;
use App\Services\Core\Tools\JuHe\Phone\PhoneService;
use App\Strategies\LocationStrategy;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Helpers\Utils;
use Illuminate\Support\Facades\Log;

class UserInsuranceListener extends AppListener
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

    private $heiniuDatas = array();

    /**
     * Handle the event.
     *
     * @param  AppEvent $event
     * @return void
     */
    public function handle(AppEvent $event)
    {
        try {
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_HEINIU_NID);
            //SLogger::getStream()->info('黑牛type', ['data' => $type]);
            //黑牛保险推广关闭
            if (!empty($type)) //黑牛保险推广关闭
            {
                //查询数据
                $spread = UserSpreadFactory::getSpread($event->data['mobile']);
                //SLogger::getStream()->info('黑牛spreadData', ['data' => $spread]);
                //数据处理
                $spread = SpreadStrategy::getSpreadDatas($spread, $type, $event->data);
                //SLogger::getStream()->info('黑牛spread_nid', ['data' => $spread]);
                // 推广统计
                event(new UserSpreadCountEvent($spread));

                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($spread);
                //没有流水推送，状态为2推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status']) {
                    $spread['spread_log_id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    //SLogger::getStream()->info('黑牛', ['data' => $spread]);
                    $this->pushInsuranceData($spread);
                }
            }
        } catch (\Exception $exception) {
            SLogger::getStream()->error('黑牛保险发送失败-catch');
            SLogger::getStream()->error($exception->getMessage());
        }
    }

    /**
     * 黑牛保险限制条件
     *
     * @param array $spread
     * @return bool
     */
    public function pushInsuranceData($spread = [])
    {
        //分发类型不存在
        if (0 == $spread['type_id']) {
            return false;
        }

        //发放时间限制
        if (SpreadStrategy::checkValidateTime($spread)) {
            //性别限制
            if (SpreadStrategy::checkSpreadSex($spread)) //性别限制
            {
                //年龄限制
                $age = Utils::getAge($spread['birthday']);
                //修改时间格式 1992-02-02 00:00:00 => 1992-02-02
                $spread['birthday'] = DateUtils::getBirthday($spread['birthday']);
                $spread['age'] = $age;
                if (SpreadStrategy::checkSpreadAge($spread)) //年龄限制
                {
                    //城市限制
                    if ($this->checkCityAgain($spread)) //城市限制
                    {
                        $spread['city'] = $this->heiniuDatas['city'];
                        $spread['age'] = $age;
                        //判断延迟表中书否存在数据
                        $checkBatch = UserSpreadFactory::checkIsUserSpreadBatch($spread);
                        if ($spread['batch_status'] == 1 && empty($checkBatch)) //开启,延迟推送
                        {
                            //增加次数
                            CacheFactory::incrementCacheToOneloan($spread);
                            $this->waitPush($spread);
                        } elseif ($spread['batch_status'] == 0)  //立即推送
                        {
                            //增加次数
                            CacheFactory::incrementCacheToOneloan($spread);
                            $this->nowPush($spread);
                        }
                    }
                }
            }
        }
    }

    /**
     * 立即推送
     *
     * @param $data
     */
    private function nowPush($data)
    {
        //访问
        $res = HeiniuService::insurance($data);
        //Log::info('黑牛保险推送结果', ['res' => $res, 'code' => 10000]);
        $params['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $params['mobile'] = isset($data['mobile']) ? $data['mobile'] : '';
        $params['id'] = isset($data['id']) ? $data['id'] : 0;
        $params['type_id'] = isset($data['type_id']) ? $data['type_id'] : 0;
        $params['status'] = 0;
        $params['message'] = '数据为空';
        $data['response_code'] = 0;
        //对接口进行数据处理
        //SLogger::getStream()->info('heiniu', ['res' => $res]);
        if (isset($res['error_code']) && $res['error_code'] == 0) {
            // 成功
            $params['message'] = isset($res['error_msg']) ? $res['error_msg'] : '赠险成功';
            $params['status'] = 1;
            $data['response_code'] = 1;
        } else {
            // 失败
            $params['message'] = isset($res['error_msg']) ? $res['error_msg'] : '赠险失败';
            $params['status'] = 0;
            $data['response_code'] = 2;
        }

        // 创建赠险流水，如果流水存在但是状态为2，只更新不推送
        //是否是延迟推送流水  0不是，1是
        $data['batch_status'] = 0;
        if (!UserSpreadFactory::checkIsSpread($data)) {
            $data['result'] = $params['result'];
            $data['message'] = $params['message'];
            $data['status'] = $params['status'];
            //SLogger::getStream()->info('heiniu_data', ['data' => $data]);
            $params['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($data);
        } else {
            // 更新分发数据状态
            UserSpreadFactory::insertOrUpdateUserSpreadLog($params);
        }

        // 更新推送次数等数据
        $data['type_nid'] = SpreadNidConstant::SPREAD_HEINIU_NID;
        SpreadStrategy::updateSpreadCounts($data);
    }

    /**
     * 延迟推送
     *
     * @param $data
     */
    private function waitPush($data)
    {
        // 延迟推送信息&时间
        $data['status'] = 3;
        $data['message'] = '延迟推送';
        $data['result'] = '';
        $pushTime = time() + ($data['batch_interval'] * 60);
        $data['send_at'] = date('Y-m-d H:i:s', $pushTime);

        //插入延迟表中
        UserSpreadFactory::insertSpreadBatch($data);
    }

    /**
     * 检查年龄
     *
     * @param $age
     * @return bool
     */
    private function checkAge($age)
    {
        if ($age >= 25 && $age <= 47) {
            return true;
        }

        return false;
    }

    /**
     * 检查符合城市
     *
     * @param $mobile
     * @return bool
     */
    private function checkCity($mobile)
    {
        return true;
        $city = '';
        $arrCity = SpreadStrategy::getHeiNiuCity();
        $phoneInfo = PhoneService::getPhoneInfo($mobile);
        if (!empty($phoneInfo)) {
            if (isset($phoneInfo['att']) && !empty($phoneInfo['att'])) {
                $citys = explode(',', $phoneInfo['att']);
                $city = array_pop($citys);
            }
        }

        if (in_array($city, $arrCity)) {
            return true;
        }

        return false;
    }

    /**
     * 手机号定位城市
     * @param array $data
     * @return array|bool
     */
    private function checkCityAgain($data = [])
    {
        $city = '';
        $phoneInfo = JuhePhoneService::getPhoneInfo($data['mobile']);
        //SLogger::getStream()->info('黑牛手机号定位', ['info' => $phoneInfo]);
        if (!empty($phoneInfo)) {
            //自治州常量定义
            $continents = SpreadConstant::SPREAD_DEVICE;
            if (array_key_exists(trim($phoneInfo['city']), $continents)) //自治州
            {
                $city = $continents[$phoneInfo['city']];

            } else  //市
            {
                //手机号定位城市没有'市'字
                $city = isset($phoneInfo['city']) ? $phoneInfo['city'] : '';
                if (empty($city)) {
                    $city = isset($phoneInfo['province']) ? $phoneInfo['province'] : '';
                }
                $city = stripos($city, '市') ? $city : $city . '市';
            }
        }

        $data['city'] = $city;
        $this->heiniuDatas['city'] = $city;
        $citys = UserSpreadFactory::fetchUserSpreadAreasByTypeIdAndCityName($data);
        //SLogger::getStream()->info('黑牛手机号返回值', ['data' => $citys]);
        //有城市限制
        if (empty($citys)) {
            return false;
        }
        //超过城市限制条数
        if ($citys['today_limit'] > 0 && $citys['today_limit'] <= $citys['today_total']) {
            return false;
        }

        return true;
    }
}
