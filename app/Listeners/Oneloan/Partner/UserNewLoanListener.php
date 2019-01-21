<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Constants\XinyidaiConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Orm\UserSpread;
use App\Models\Orm\UserSpreadType;
use App\Services\Core\Oneloan\Xinyidai\XinyidaiService;
use App\Services\Core\Tools\JuHe\Phone\JuhePhoneService;
use App\Services\Core\Tools\JuHe\Phone\PhoneService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Helpers\Utils;
use Illuminate\Support\Facades\Log;

/**
 * 新一贷
 *
 * Class UserNewLoanListener
 * @package App\Listeners\V1
 */
class UserNewLoanListener extends AppListener
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
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_XINYIDAI_NID);
            if (!empty($type)) {

                //查询数据
                $spread = UserSpreadFactory::getSpread($event->data['mobile']);
                //数据处理
                $spread = SpreadStrategy::getSpreadDatas($spread, $type, $event->data);
                // 推广统计
                event(new UserSpreadCountEvent($spread));

                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($spread);
                //没有流水推送，状态为2推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status']) {
                    $spread['spread_log_id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    //SLogger::getStream()->info('新一贷', ['data' => $spread]);
                    $this->pushNewLoanData($spread);
                }
            }
        } catch (\Exception $exception) {
            SLogger::getStream()->error('新一贷发送失败-catch');
            SLogger::getStream()->error($exception->getMessage());
        }
    }

    /**
     * 处理新一贷数据
     *
     * @param $spread
     * @return bool
     */
    public function pushNewLoanData($spread)
    {
        $typeNid = SpreadNidConstant::SPREAD_XINYIDAI_NID;
        //24小时限制
//        $limit = SpreadStrategy::getPushProductLimit($typeNid, $data['mobile']);
//        if ($limit) {
//            return true;
//        }

        //分发类型不存在
        if (0 == $spread['type_id']) {
            return false;
        }

        //发放时间限制
        if (SpreadStrategy::checkValidateTime($spread)) //发放时间限制
        {
            //性别限制
            if (SpreadStrategy::checkSpreadSex($spread)) //性别限制
            {
                //获取城市和获取城市编码
                $age = Utils::getAge($spread['birthday']);
                $spread['age'] = $age;
                //信用卡信息
                $spread['hasCreditCard'] = $spread['has_creditcard'];
                //城市编码
                $cityInfo = UserSpreadFactory::checkSpreadCity($spread);
                $spread['city_code'] = isset($cityInfo['city_code']) ? $cityInfo['city_code'] : '';
                //年龄限制
                if (SpreadStrategy::checkSpreadAge($spread)) //年龄限制
                {
                    //有信用卡
                    if ($this->checkHasCreditcard($spread['has_creditcard'])) {
                        //用户条件
                        if ($this->checkCondition($spread)) {
                            //筛选城市
                            if ($spread['city_code'] > 0) {
                                //年龄处理
                                $spread['age'] = '23-55岁';
                                //判断延迟表中书否存在数据
                                $checkBatch = UserSpreadFactory::checkIsUserSpreadBatch($spread);
                                if ($spread['batch_status'] == 1 && empty($checkBatch)) //开启,延迟推送
                                {
                                    //增加次数
                                    CacheFactory::incrementCacheToOneloan($spread);
                                    $this->waitPush($spread);
                                } elseif ($spread['batch_status'] == 0)  //立即推送
                                {
                                    //推广总量限制
                                    if ($spread['total'] < $spread['limit'] or 0 == $spread['limit'])
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
            }
        }
    }

    /**
     * 立即发送
     *
     * @param $spread
     */
    private function nowPush($spread)
    {
        //  推送service
        $res = XinyidaiService::spread($spread);

        //未推送
        $params['status'] = 0;
        $params['message'] = '数据为空';
        $spread['response_code'] = 0;
        if (isset($res['responseCode'])) {
            if ($res['responseCode'] == '000000') {
                $params['message'] = '操作成功';
                $params['status'] = 1;
                $spread['response_code'] = 1;
            } else {
                $params['message'] = '操作失败';
                $params['status'] = 0;
                $spread['response_code'] = 2;
            }
        }
        //已存在流水表数据主键id
        $params['id'] = $spread['id'];
        $params['type_id'] = $spread['type_id'];
        $params['mobile'] = $spread['mobile'];
        $params['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);

        // 创建赠险流水，如果流水存在但是状态为2，只更新不推送
        //是否是延迟推送流水  0不是，1是
        $spread['batch_status'] = 0;
        if (!UserSpreadFactory::checkIsSpread($spread)) {
            $spread['result'] = $params['result'];
            $spread['message'] = $params['message'];
            $spread['status'] = $params['status'];
            $params['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        } else {
            // 更新分发数据状态
            UserSpreadFactory::insertOrUpdateUserSpreadLog($params);
        }

        // 更新推送次数等数据
        $spread['type_nid'] = SpreadNidConstant::SPREAD_XINYIDAI_NID;
        SpreadStrategy::updateSpreadCounts($spread);
    }

    /**
     * 延迟推送
     *
     * @param $spread
     */
    private function waitPush($spread)
    {
        // 创建流水
//        $spread['type_id'] = $data['type_id'];
        $spread['status'] = 3;
        $spread['message'] = '延迟推送';
        $spread['result'] = '';
        $pushTime = time() + ($spread['batch_interval'] * 60);
        $spread['send_at'] = date('Y-m-d H:i:s', $pushTime);

        //插入延迟表中
        UserSpreadFactory::insertSpreadBatch($spread);
    }

    /**
     * 有信用卡
     * @param $hasCreditcard
     * @return bool
     */
    private function checkHasCreditcard($hasCreditcard)
    {
        if ($hasCreditcard == 1) {
            return true;
        }

        return false;
    }

    /**
     * 有房贷推送
     * @param string $house
     * @return bool
     */
    private function checkHouse($house = '')
    {
        if ($house == '001') {
            return true;
        }

        return false;
    }

    /**
     * 判断年龄
     *
     * @param $age
     * @return bool
     */
    private function checkAge($age)
    {
        if ($age >= 23 && $age <= 55) {
            return true;
        }

        return false;
    }

    /**
     * 判断用户身份条件
     *
     * @param $data
     * @return bool
     */
    private function checkCondition($data)
    {
        //寿险、房产、公积金、有其一
        if ($data['has_insurance'] > 0 or in_array($data['house_info'], ['001', '002']) or in_array($data['accumulation_fund'], ['001', '002'])) {
            return true;
        }

        return false;
    }

    /**
     * 判断城市
     * @param array $data
     * @return int
     */
    private function checkCity($data = [])
    {
        //城市信息
        $citys = UserSpreadFactory::fetchUserSpreadAreasByTypeIdAndCityName($data);
        //有城市限制
        if (empty($citys)) {
            return false;
        }
        //超过城市限制条数
        if ($citys['today_limit'] > 0 && $citys['today_limit'] <= $citys['today_total']) {
            return false;
        }
        return $citys['city_code'];
    }

    /**
     * 重新获取城市判断
     *
     * @param $mobile
     * @return int
     */
    private function checkCityAgain($mobile)
    {
        $city = '';
        $arrCity = XinyidaiConstant::PUSH_A_SORT_CITY;
        $phoneInfo = JuhePhoneService::getPhoneInfo($mobile);
        if (!empty($phoneInfo)) {
            $city = isset($phoneInfo['city']) ? $phoneInfo['city'] : '';
            if (empty($city)) {
                $city = isset($phoneInfo['province']) ? $phoneInfo['province'] : '';
            }
        }

        foreach ($arrCity as $key => $code) {
            if (strpos($city, $key) !== false) {
                return $code;
            }
        }

        return -1;
    }
}
