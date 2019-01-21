<?php

namespace App\Listeners\Oneloan\Partner;

use App\Constants\SpreadNidConstant;
use App\Events\AppEvent;
use App\Events\Oneloan\Partner\UserSpreadCountEvent;
use App\Events\Oneloan\Partner\UserSpreadEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Zhongtengxin\Config\ZhongtengxinConfig;
use App\Services\Core\Oneloan\Zhongtengxin\ZhongtengxinService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;

/**
 * 中腾信
 * Class UserZhongtengxinListener
 * @package App\Listeners\V1
 */
class UserZhongtengxinListener extends AppListener
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
            $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_ZHONGTENGXIN_NID);
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
                    //SLogger::getStream()->info('中腾信',['data'=>$spread]);
                    $this->pushZhongtengxinData($spread);
                }
            }
        } catch (\Exception $exception) {
            SLogger::getStream()->error('中腾信发送失败-catch');
            SLogger::getStream()->error($exception->getMessage());
        }
    }

    /**
     * 数据处理
     * @param $spread
     * @return bool
     */
    public function pushZhongtengxinData($spread)
    {
        $typeNid = SpreadNidConstant::SPREAD_ZHONGTENGXIN_NID;
        //24小时限制
//        $limit = SpreadStrategy::getPushProductLimit($typeNid, $data['mobile']);
//        if ($limit) {
//            return true;
//        }

        //分发类型不存在
        if (0 == $spread['type_id'])
        {
            return false;
        }

        //发放时间限制
        if (SpreadStrategy::checkValidateTime($spread)) //发放时间限制
        {
            //性别限制
            if(SpreadStrategy::checkSpreadSex($spread)) //性别限制
            {
                //年龄限制
                $age = Utils::getAge($spread['birthday']);
                $spread['age'] = $age;
                if (SpreadStrategy::checkSpreadAge($spread)) //年龄限制
                {
                    //借款金额1万以上
                    if ($this->checkMoney($spread['money']))
                    {
                        //工作证明:社保、公积金、银行转账选其一
                        if ($this->checkCondition($spread))
                        {
                            //工作类型：上班族、公务员、私营企业主（营业执照注册时间满一年以上）
                            if ($this->checkWork($spread))
                            {
                                //城市限制
                                if (UserSpreadFactory::checkSpreadCity($spread))
                                {
//                                    $data['age'] = $age;
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

    }

    /**
     * 立刻推送
     *
     * @param $spread
     */
    private function nowPush($spread)
    {
        //推送类型id
//        $spread['type_id'] = $data['type_id'];
        //推送中腾信
        $res = ZhongtengxinService::spread($spread);
        //SLogger::getStream()->info('中腾信', ['info' => $spread, 'res' => $res]);
        //处理结果
        //已存在流水表数据主键id
        $params['id'] = $spread['spread_log_id'];
        $params['type_id'] = $spread['type_id'];
        $params['mobile'] = $spread['mobile'];
        $params['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $params['status'] = 0;
        $params['message'] = '数据为空';
        $spread['response_code'] = 0;
        if (isset($res['result']) && $res['result'] == 'success') {
            $params['status'] = 1;
            $params['message'] = '成功';
            $spread['response_code'] = 1;
        } else {
            $params['status'] = 0;
            $params['message'] = '失败';
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
        $spread['type_nid'] = SpreadNidConstant::SPREAD_ZHONGTENGXIN_NID;
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
        $spread['status'] = 3;
        $spread['message'] = '延迟推送';
        $spread['result'] = '';
        $pushTime = time() + ($spread['batch_interval'] * 60);
        $spread['send_at'] = date('Y-m-d H:i:s', $pushTime);

        //插入延迟表中
        UserSpreadFactory::insertSpreadBatch($spread);
    }

    /**
     * 借款金额1w以上
     * @param $money
     * @return bool
     */
    private function checkMoney($money)
    {
        if ($money >= 10000) {
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
        //月收入2000以上
        if (in_array($data['salary'], ['102', '103', '104', '105', '106'])) {
            //工作证明:社保[社会保险]、公积金、银行转账选其一　　　　　
            if ($data['social_security'] > 0 or in_array($data['accumulation_fund'], ['001', '002']) or in_array($data['salary_extend'], ['001'])) {
                return true;
            }
        }


        return false;
    }

    /**
     * 判断工作类型
     *
     * @param $data
     * @return bool
     */
    private function checkWork($data)
    {
        //工作类型：上班族、公务员、私营企业主（营业执照注册时间满一年以上）　
        if (in_array($data['occupation'], ['001', '002', '003'])) {
            //私营企业主（营业执照注册时间满一年以上）　
            if ('003' == $data['occupation'] && in_array($data['business_licence'], ['002'])) {
                return true;
            } else {
                return true;
            }
        }

        return false;
    }

}