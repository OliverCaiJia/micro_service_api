<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Constants\XiaoxiaoConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadEvent;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Services\Core\Oneloan\Xiaoxiaojinrong\Config\XiaoxiaojinrongConfig;
use App\Services\Core\Oneloan\Xiaoxiaojinrong\XiaoxiaoService;
use App\Services\Core\Oneloan\Xiaoxiaojinrong\XiaoxiaojinrongService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Services\Core\Tools\JuHe\Phone\JuhePhoneService;
use Illuminate\Support\Facades\Log;
use App\Helpers\Utils;

/**
 * 小小金融2监听器
 * Class UserFinanceListener
 * @package App\Listeners\V1
 */
class UserXiaoxiaoListener extends AppListener
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
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_XIAOXIAO_SECOND_NID);

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
                    //SLogger::getStream()->info('小小2', ['data' => $spread]);
                    $this->pushFinanceData($spread);
                }
            }
        } catch (\Exception $exception) {
            SLogger::getStream()->error('小小金融2发送失败-catch');
            SLogger::getStream()->error($exception->getMessage());
        }

    }

    /**
     * 条件判断
     *
     * @param array $spread
     * @return bool
     */
    public function pushFinanceData($spread = [])
    {
        if ($spread['type_id'] == 0) {
            return false;
        }

        //发放时间限制
        if (SpreadStrategy::checkValidateTime($spread)) {
            //性别限制
            if (SpreadStrategy::checkSpreadSex($spread)) {
                $age = Utils::getAge($spread['birthday']);
                $spread['age'] = $age;
                //年龄限制
                if (SpreadStrategy::checkSpreadAge($spread)) {
                    //贷款额度限制
                    if ($this->checkMoney($spread['money'])) {
                        //个人条件限制
                        if ($this->checkCondition($spread)) {
                            //城市限制
                            if (UserSpreadFactory::checkSpreadCity($spread)) {
                                //判断延迟表中书否存在数据
                                $checkBatch = UserSpreadFactory::checkIsUserSpreadBatch($spread);
                                if ($spread['batch_status'] == 1 && empty($checkBatch)) //开启,延迟推送
                                {
                                    //增加次数
                                    CacheFactory::incrementCacheToOneloan($spread);
                                    $this->waitPush($spread);
                                } elseif ($spread['batch_status'] == 0)  //立即推送
                                {
                                    //推送总量限制
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
     * @param array $spread
     */
    private function nowPush($spread = [])
    {
        // 推广service
        $res = XiaoxiaoService::spread($spread);

        //已存在流水表数据主键id
        $params['id'] = $spread['spread_log_id'];
        $params['type_id'] = $spread['type_id'];
        $params['mobile'] = $spread['mobile'];
        $params['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        //推送失败
        $params['status'] = 0;
        $params['message'] = '数据为空';
        $spread['response_code'] = 0;
        //SLogger::getStream()->info('finance', ['res' => $res]);
        if (isset($res['returnCode'])) {
            $params['message'] = XiaoxiaojinrongConfig::getMessage($res['returnCode']);
            if ($res['returnCode'] == '000') {
                $params['status'] = 1;
                $spread['response_code'] = 1;
            } else {
                $params['status'] = 0;
                $spread['response_code'] = 2;
            }
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
        $spread['type_nid'] = SpreadNidConstant::SPREAD_XIAOXIAO_SECOND_NID;
        SpreadStrategy::updateSpreadCounts($spread);
    }

    /**
     * 延迟推送
     *
     * @param array $spread
     */
    private function waitPush($spread = [])
    {
        // 延迟推送信息
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
        if (in_array($data['salary'], ['102', '103', '104', '105', '106'])) {
            //有保险　公积金　有房 取其一　　　　　　　
            if ($data['has_insurance'] > 0 or in_array($data['accumulation_fund'], ['001', '002']) or in_array($data['house_info'], ['001', '002'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * 金额限制
     *
     * @param $money
     * @return bool
     */
    private function checkMoney($money)
    {
        if ($money >= 30000) {
            return true;
        }

        return false;
    }

    /**
     * 数据库存储城市是否符合规则
     *
     * @param array $data
     * @return bool
     */
    private function checkCityBySql($data = [])
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
}
