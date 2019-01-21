<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Cache\CommonCache;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Niwodai\Miaola\Config\MiaolaConfig;
use App\Services\Core\Oneloan\Niwodai\Miaola\MiaolaService;
use App\Services\Core\Oneloan\Niwodai\NiwodaiService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use Illuminate\Support\Facades\Log;

/**
 * 你我贷-秒啦
 *
 * Class UserNiwodaiListener
 * @package App\Listeners\V1
 */
class UserMiaolaListener extends AppListener
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
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_MIAOLA_NID);
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
                    //SLogger::getStream()->info('秒啦', ['data' => $spread]);
                    $this->pushNiwodaiData($spread);
                }
            }
        } catch (\Exception $exception) {
            SLogger::getStream()->error('你我贷-秒啦发送失败-catch');
            SLogger::getStream()->error($exception->getMessage());
        }
    }

    /**
     * 数据处理
     *
     * @param $spread
     * @return bool
     */
    public function pushNiwodaiData($spread)
    {
        $typeNid = SpreadNidConstant::SPREAD_MIAOLA_NID;
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
        if (SpreadStrategy::checkValidateTime($spread)) {
            //性别限制
            if (SpreadStrategy::checkSpreadSex($spread)) {
                //年龄限制
                $age = Utils::getAge($spread['birthday']);
                $spread['age'] = $age;
                if (SpreadStrategy::checkSpreadAge($spread)) {
                    //借款金额5万以上
                    if ($this->checkMoney($spread['money'])) {
                        //条件限制:有寿险保单、有车、有房选其一
                        if ($this->reCheckCondition($spread)) {
                            //城市限制
                            $cityInfo = UserSpreadFactory::checkSpreadCity($spread);
                            $spread['cityname'] = isset($cityInfo['city_name']) ? $cityInfo['city_name'] : '';
                            if ($spread['cityname']) {

                                //判断延迟表中书否存在数据
                                $checkBatch = UserSpreadFactory::checkIsUserSpreadBatch($spread);

                                if ($spread['batch_status'] == 1 && empty($checkBatch)) //开启,延迟推送
                                {
                                    //增加次数
                                    CacheFactory::incrementCacheToOneloan($spread);
                                    $this->waitPush($spread);
                                } elseif ($spread['batch_status'] == 0)  //立即推送
                                {
                                    //若当前limit = 0(未设置限额) 可推送; 若当前未到限额 可推送
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
     * 立刻推送
     *
     * @param $spread
     */
    private function nowPush($spread = [])
    {
        //推送你我贷
        $spreadParams = MiaolaConfig::getParams($spread);

        $res = MiaolaService::apply($spreadParams);
        //我们好像所有的都是OPEN55020这个错误码，我就用这个了，到时候再次请求一次，弥补一下验签错误的情况
        if (isset($res['success']) && $res['success'] == 0 && $res['errCode'] == 'OPEN55020') {
            //删除cache缓存，重新获取
            CommonCache::delCache(CommonCache::NIWODAI_TOKEN);
            //重新请求接口
            $res = MiaolaService::apply($spreadParams);
        }

        //处理结果
        //已存在流水表数据主键id
        $params['id'] = $spread['spread_log_id'];
        $params['type_id'] = $spread['type_id'];
        $params['mobile'] = $spread['mobile'];
        $params['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $params['status'] = 0;
        $params['message'] = '数据为空';
        $spread['response_code'] = 0;
        if (isset($res['success']) && $res['success'] == 1) {
            $params['status'] = 1;
            $params['message'] = '成功';
            $spread['response_code'] = 1;
        } else {
            $params['status'] = 0;
            $params['message'] = isset($res['errMsg']) ? $res['errMsg'] : '失败';
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
        $spread['type_nid'] = SpreadNidConstant::SPREAD_MIAOLA_NID;
        SpreadStrategy::updateSpreadCounts($spread);
    }

    /**
     * 延迟推送
     * @param $spread
     */
    private function waitPush($spread)
    {
        // 创建流水
        $spread['status'] = 3;
        $spread['message'] = '延迟推送';
        $spread['result'] = '';
        $pushTime = time() + ($spread['batch_interval'] * 60);
        $spread['send_at'] = date('Y-m-d H:i:s', $pushTime);

        //插入流水
        //$spread['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        //插入延迟表中
        UserSpreadFactory::insertSpreadBatch($spread);
        //更新推送次数等数据
//        UserSpreadFactory::updateSpreadTypeTotalOnly(SpreadNidConstant::SPREAD_DONGFANG_NID);
    }

    /**
     * 判断年龄
     *
     * @param $age
     * @return bool
     */
    private function checkAge($age)
    {
        return true;

//        if ($age >= 25 && $age <= 55) {
//            return true;
//        }
//
//        return false;
    }

    /**
     * 1000-30000
     * @param $money
     * @return bool
     */
    private function checkMoney($money)
    {
        if ($money >= 1000 && $money <= 30000) {
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
        //有寿险保单、有车、有房选其一　　　　　　
        if ($data['has_insurance'] > 0 or in_array($data['car_info'], ['001', '002']) or in_array($data['house_info'], ['001', '002'])) {
            return true;
        }

        return false;
    }

    /**
     * 检查城市
     * @param array $data
     * @return bool
     */
    private function checkCity($data = [])
    {
        //SLogger::getStream()->info('东方信息', ['info' => $data]);
        //城市信息
        $citys = UserSpreadFactory::fetchUserSpreadAreasByTypeIdAndCityName($data);
        //SLogger::getStream()->info('东方城市匹配', ['info' => $citys]);
        //有城市限制
        if (empty($citys)) {
            return false;
        }
        //超过城市限制条数
        if ($citys['today_limit'] > 0 && $citys['today_limit'] <= $citys['today_total']) {
            return false;
        }
        return $citys['city_pinyin'];
    }

    /**
     * 条件
     *
     * @param array $data
     * @return bool
     */
    private function reCheckCondition($data = [])
    {
        return true;
    }
}
