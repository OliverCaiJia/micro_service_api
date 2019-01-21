<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Constants\ZhudaiwangConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Events\Oneloan\Partner\UserSpreadEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Orm\UserSpread;
use App\Models\Orm\UserSpreadType;
use App\Services\Core\Oneloan\Zhudaiwang\Config\ZhudaiwangConfig;
use App\Services\Core\Oneloan\Zhudaiwang\ZhudaiwangService;
use App\Services\Core\Tools\JuHe\Phone\JuhePhoneService;
use App\Services\Core\Tools\JuHe\Phone\PhoneService;
use App\Strategies\SpreadStrategy;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 助贷网
 * Class UserLoanListener
 * @package App\Listeners\V1
 */
class UserLoanListener extends AppListener
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
        try{
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_ZHUDAIWANG_NID);
            if (!empty($type))
            {
                //查询数据
                $spread = UserSpreadFactory::getSpread($event->data['mobile']);
                //数据处理
                $spread = SpreadStrategy::getSpreadDatas($spread, $type, $event->data);
                // 推广统计
                event(new UserSpreadCountEvent($spread));

                //查询未推送信息
                $spreadLogInfo = UserSpreadFactory::fetchSpreadLogInfoByMobileAndTypeId($spread);
                //没有流水推送，状态为2推送
                if (!$spreadLogInfo || 2 == $spreadLogInfo['status'])
                {
                    $spread['spread_log_id'] = isset($spreadLogInfo['id']) ? $spreadLogInfo['id'] : 0;
                    //SLogger::getStream()->info('助贷网', ['data' => $spread]);
                    $this->pushLoanData($spread);
                }
            }
        }catch (\Exception $exception) {
            SLogger::getStream()->error('助贷网发送失败-catch');
            SLogger::getStream()->error($exception->getMessage());
        }
    }

    /**
     * 数据处理
     *
     * @param $spread
     * @return bool
     */
    public function pushLoanData($spread)
    {
        $typeNid = SpreadNidConstant::SPREAD_ZHUDAIWANG_NID;
        //24小时限制
//        $limit = SpreadStrategy::getPushProductLimit($typeNid, $data['mobile']);
//        if ($limit) {
//            return true;
//        }

        if (0 == $spread['type_id'])
        {
            return false;
        }

        //发放时间限制
        if (SpreadStrategy::checkValidateTime($spread))
        {
            //性别限制
            if(SpreadStrategy::checkSpreadSex($spread))
            {
                $age = Utils::getAge($spread['birthday']);
                $spread['age'] = $age;
                //年龄限制
                if (SpreadStrategy::checkSpreadAge($spread))
                {
                    //自身条件限制
                    if ($this->checkCondition($spread))
                    {
                        //城市限制
                        if (UserSpreadFactory::checkSpreadCity($spread))
                        {
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

    /**
     * 立即推送
     *
     * @param $spread
     */
    private function nowPush($spread)
    {
        // 创建流水
//        $spread['type_id'] = $data['type_id'];
        //  推送助贷网
        $res = ZhudaiwangService::spread($spread);

        //处理结果
        //已存在流水表数据主键id
        $params['id'] = $spread['spread_log_id'];
        $params['type_id'] = $spread['type_id'];
        $params['mobile'] = $spread['mobile'];
        $params['status'] = 0;
        $params['message'] = '数据为空';
        $spread['response_code'] = 0;
        $params['message'] = ZhudaiwangConfig::getMessage(intval($res));
        $params['result'] = $res;
        if (intval($res) > 1000000) {
            $params['status'] = 1;
            $spread['response_code'] = 1;
        } else {
            $params['status'] = 0;
            $spread['response_code'] = 2;
        }

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
        $spread['type_nid'] = SpreadNidConstant::SPREAD_ZHUDAIWANG_NID;
        SpreadStrategy::updateSpreadCounts($spread);
    }

    /**
     * 延迟推送
     *
     * @param $spread
     * @param $age
     */
    private function waitPush($spread)
    {
        // 创建推广流水
        $spread['status'] = 3;
        $spread['message'] = '延迟推送';
        $spread['result'] = '';
        $pushTime = time() + ($spread['batch_interval'] * 60);
        $spread['send_at'] = date('Y-m-d H:i:s', $pushTime);

        //插入延迟表中
        UserSpreadFactory::insertSpreadBatch($spread);
    }

    /**
     * 判断年龄
     *
     * @param $age
     * @return bool
     */
    private function checkAge($age)
    {
        if ($age >= 25 && $age <= 55) {
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
        //  有房，有车，有寿险保单，有公积金，微粒贷额度在2万以上（微粒贷有就推）（必须至少具备其中一项）；　　　　　　
        if (in_array($data['has_insurance'], ['1', '2'])
            or in_array($data['car_info'], ['001', '002'])
            or in_array($data['house_info'], ['001', '002'])
            or in_array($data['accumulation_fund'], ['001', '002'])
            or $data['is_micro'] == 1) //匹配条件
        {
            return true;
        }

        return false;
    }

    /**
     * 检查城市
     * @param array $data
     * @return bool
     */
    private function checkLimitCity($data = [])
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
        return true;
    }

    /**
     * 设置redis字符串的值和过期时间
     *
     * @param $key string 键
     * @param $value string 值
     * @param $outTime  int  时间（s）秒
     * @return mixed
     */
    public function setCache($key, $value, $outTime = null)
    {
        return Cache::put($key, $value, $outTime);
    }

    /**
     * 检查城市
     * @param array $data
     * @return bool
     */
    private function checkCity($data = [])
    {
        $city = isset($data['city']) ? $data['city'] : '';
        $zhudaiCitys = ZhudaiwangConstant::PUSH_CITYS;

        if (in_array($city, $zhudaiCitys)) {
            return true;
        }

        return false;
    }

    /**
     * 再次检查城市
     *
     * @param string $wCity 手写的城市
     * @param string $mobile 手机号
     * @return bool
     */
    private function checkReCity($wCity, $mobile)
    {
        return true;
        $arr = ['深圳市', '上海市', '北京市', '广州市', '杭州市'];
        $city = '';
        $phoneInfo = PhoneService::getPhoneInfo($mobile);
        if (!empty($phoneInfo)) {
            if (isset($phoneInfo['style_citynm']) && !empty($phoneInfo['style_citynm'])) {
                $citys = explode(',', $phoneInfo['style_citynm']);
                $city = array_pop($citys);
            }
        }

        if (in_array($city, $arr) || in_array($wCity, $arr)) {
            return true;
        }

        return false;
    }

    /**
     * 重新获取城市验证
     *
     * @param $wCity
     * @param $mobile
     * @return bool
     */
    private function checkCityAgain($wCity, $mobile)
    {
        $arr = ['深圳', '上海', '北京', '广州', '杭州'];
        $city = '';
        $wwCity = mb_substr($wCity, 0, -1);
        $phoneInfo = JuhePhoneService::getPhoneInfo($mobile);
        if (!empty($phoneInfo)) {
            $city = isset($phoneInfo['city']) ? $phoneInfo['city'] : '';
            if (empty($city)) {
                $city = isset($phoneInfo['province']) ? $phoneInfo['province'] : '';
            }
        }

        if (in_array($city, $arr) || in_array($wwCity, $arr)) {
            return true;
        }

        return false;
    }

}
