<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Events\V1\UserSpreadEvent;
use App\Models\Factory\UserSpreadFactory;
use App\Events\V1\UserSpreadCountEvent;
use App\Models\Orm\UserSpread;
use App\Models\Orm\UserSpreadType;
use App\Services\Core\Data\Xiaoxiaojinrong\XiaoxiaojinrongService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Models\Chain\Creditcard;
use App\Services\Core\Tools\Phone\JuhePhoneService;
use App\Services\Core\Tools\Phone\PhoneService;
use Illuminate\Support\Facades\Log;
use App\Helpers\Utils;


//use App\Strategies\UserSpreadStrategy;

/**
 * 小小金融监听器
 * Class UserFinanceListener
 * @package App\Listeners\V1
 */
class UserFinanceListener extends AppListener
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
    public function handle(UserSpreadEvent $event)
    {
        $type_nid = UserSpreadFactory::SPREAD_XIAOXIAO_NID;//'spread_finance';
        
        $type = UserSpreadType::where('type_nid', $type_nid)->where('status', 1)->first();
        if (!empty($type)) {
            $event->data['type_id'] = $type ? $type->id : 0;
            $event->data['limit'] = $type ? $type->limit : 0;
            $event->data['total'] = $type ? $type->total : 0;
            
            // 插入用户数据
            UserSpreadFactory::createOrUpdateUserSpread($event->data);
            
            // 推广统计
            $spread = UserSpread::where('mobile', $event->data['mobile'])->first();
            $spread['type_id'] = $event->data['type_id'];
            event(new UserSpreadCountEvent($spread->toArray()));
            
            if (!UserSpreadFactory::checkIsSpread($event->data)) {
                $this->pushFinanceData($event->data, $spread);
            }
        }
        
    }
    
    /**
     * 小小金融处理
     *
     * @param $data
     * @return bool
     */
    public function pushFinanceData($data, $spread)
    {
        $typeNid = UserSpreadFactory::SPREAD_XIAOXIAO_NID;
        $limit = SpreadStrategy::getPushProductLimit($typeNid, $data['mobile']);
        if ($limit) {
            return true;
        }
        
        if ($data['type_id'] != 0) {
            $age = Utils::getAge($spread->birthday);
            
            if ($this->checkAge($age)) {
                if ($this->checkMoney($spread['money'])) {
                    if ($this->checkCondition($data)) {
                        //筛选城市
                        if ($this->checkCityAgain($data['mobile'])) {
                            if ($data['total'] < $data['limit']) {
                                // 创建推广流水
                                $spread['type_id'] = $data['type_id'];
                                $spread['age'] = $age;
                                $spread['id'] = 0;
                                unset($spread['status']);
                                //格式化soread
                                $params['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
                                //格式化spread
                                $spread = $this->dataFormate($spread);
                                // 推广service
                                Log::info('xiaxoxiao', ['message' => $spread, 'code' => 67333]);
                                $res = XiaoxiaojinrongService::spread($spread);
                                Log::info('xiaoxiao-service', ['message' => $res, 'code' => 893774]);
                                $params['type_id'] = $data['type_id'];
                                $params['mobile'] = $data['mobile'];
                                $params['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
                                if (isset($res['returnCode'])) {
                                    $params['message'] = XiaoxiaojinrongService::getMessage($res['returnCode']);
                                    if ($res['returnCode'] == '000') {
                                        $params['status'] = 1;
                                    } else {
                                        $params['status'] = 0;
                                    }
                                }
                                //  更新流水状态
                                UserSpreadFactory::insertOrUpdateUserSpreadLog($params);
                                
                                // 更新推送次数
                                UserSpreadFactory::updateSpreadTypeTotal($typeNid, $params['status']);
                            }
                        }
                    }
                }
            }
        }
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
        //有保险　　　　　　　　　　　　　　　　　有车　　　　　　　　　　　　　　　　　　　　　　　　　　有房　　　　　　　　　　　　　　　　　
        if ($data['has_insurance'] == 1 or in_array($data['accumulation_fund'], ['001', '002']) or in_array($data['house_info'], ['001', '002'])) {
            return true;
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
        if ($money >= 50000) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 重新获取城市判断
     *
     * @param $mobile
     * @return bool
     */
    private function checkCityAgain($mobile)
    {
        $city = '';
        $arrCity = SpreadStrategy::getXiaoxiaoCity();
        $phoneInfo = JuhePhoneService::getPhoneInfo($mobile);
        
        if (!empty($phoneInfo)) {
            $city = isset($phoneInfo['city']) ? $phoneInfo['city'] : '';
            
            if (empty($city)) {
                $city = isset($phoneInfo['province']) ? $phoneInfo['province'] : '';
                
            }
        }
        
        if (in_array($city, $arrCity)) {
            return true;
        }
        return false;
    }
    
    /*
     * 整理传递参数
     */
    public function dataFormate(&$datas = [])
    {
        $datas['birthday'] = substr($datas['birthday'], 0, 10);
        $datas['money'] = round($datas['money'] / 10000, 2);
        $datas['social_security'] = $datas['social_security'] ? 1 : 2;
        $datas['accumulation_fund'] = $datas['accumulation_fund'] == '000' ? 2 : 1;
        $datas['house_info'] = $datas['house_info'] == '000' ? 2 : 1;
        $datas['occupation'] = SpreadStrategy::getworkType($datas['occupation']);
        $datas['car_info'] = SpreadStrategy::getCarType($datas['car_info']);
        $datas['salary_extend'] = SpreadStrategy::getSalaryExtend($datas['salary_extend']);
        $datas['ip'] = Utils::ipAddress();
        
        return $datas;
        
    }
}
