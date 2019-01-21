<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Events\V1\UserSpreadEvent;
use App\Events\V1\UserSpreadCountEvent;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Orm\UserSpread;
use App\Models\Orm\UserSpreadType;
use App\Services\Core\Data\Oxygendai\OxygendaiService;
use App\Services\Core\Tools\Phone\JuhePhoneService;
use App\Services\Core\Tools\Phone\PhoneService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Helpers\Utils;
use App\Models\Factory\OxygenDaiFactory;
use Log;

class UserOxygendaiListener extends AppListener
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
     * @param  AppEvent  $event
     * @return void
     */
    public function handle(UserSpreadEvent $event)
    {
        $type_nid = UserSpreadFactory::SPREAD_OXYGENDAI_NID;
        $type = UserSpreadType::where('type_nid', $type_nid)->where('status', 1)->first();
        if(!empty($type))
        {
            $event->data['type_id'] = $type ? $type->id : 0;
            $event->data['limit'] = $type ? $type->limit : 0;
            $event->data['total'] = $type ? $type->total : 0;

            // 插入用户数据
            UserSpreadFactory::createOrUpdateUserSpread($event->data);

            // 推广统计
            $spread = UserSpread::where('mobile', $event->data['mobile'])->first();
            $spread['type_id'] = $event->data['type_id'];
            event(new UserSpreadCountEvent($spread->toArray()));

            if(!UserSpreadFactory::checkIsSpread($event->data))
            {
                $this->pushOxygendaiData($event->data, $spread);
            }
        }
    }

    /**
     * 处理氧气贷数据
     *
     * @param $data
     * @return bool
     */
    public function pushOxygendaiData($data, $spread)
    {
        $typeNid = UserSpreadFactory::SPREAD_OXYGENDAI_NID;//'spread_oxygendai';
        $limit = SpreadStrategy::getPushProductLimit($typeNid, $data['mobile']);
        if($limit)
        {
            return true;
        }

        if ($data['type_id'] != 0)
        {
            //获取城市和获取城市编码
            $age = Utils::getAge($spread->birthday);
            $data['hasCreditCard'] = $spread->has_creditcard;
            //年龄
            if($this->checkAge($age))
            {
                //用户条件
                if($this->checkCondition($data))
                {
                    //筛选城市
                    if($this->checkCityAgain($data['mobile']))
                    {
                        if($data['total'] < $data['limit'])
                        {
                            // 创建流水
                            $spread['type_id'] = $data['type_id'];
                            $spread['age'] = $age;
                            $spread['id'] = 0;
                            unset($spread['status']);
                            $params['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
                            
                            //使用批量接口
                            $spreads = array($spread);
                            $res = OxygendaiService::spreadList($spreads);
                            $params['status'] = 0;
                            if (isset($res['ret'])) {
                                if ($res['ret'] == '0') {
                                    if(isset($res['data']['isSuccess'])){
                                        if($res['data']['isSuccess'] == 'T'){
                                            $params['message'] = '操作成功';
                                            $params['status'] = 1;
                                        }else{
                                            $params['message'] = $res['data']['errMsg'];
                                        }
                                     
                                    }
                                    
                                } else {
                                    if($res['ret'] == '13002' || $res['ret'] == '13012'){
                                        //删除tokenid
                                        OxygenDaiFactory::delCache(OxygenDaiFactory::TOKENID);
                                    }
                                    $params['message'] = $res['msg'];
                                }
                            }
                            $params['type_id'] = $data['type_id'];
                            $params['mobile'] = $data['mobile'];
                            $params['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
                            //更新流水
                            UserSpreadFactory::insertOrUpdateUserSpreadLog($params);

                            // 更新推送次数等数据
                            UserSpreadFactory::updateSpreadTypeTotal($typeNid, $params['status']);
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
        if($age >=23 && $age <=55){
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
        //有信用卡                         工作时间在1年以内或者一年以上
        if($data['hasCreditCard'] == 1 && (in_array($data['work_hours'],['002','003']) || $data['business_licence'] == '002'))
        {
            //有保险　　　　　　　　　　　　　　　　                      有车　　　　　　　　　　　　　　　　　　　　　　　　　　有房　　　　　　　　　　　　　　　　　
            if($data['has_insurance'] == 1  or in_array($data['car_info'], ['001', '002']) or in_array($data['house_info'], ['001', '002']))
            {
                return true;
            }
        
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
        $arrCity = SpreadStrategy::getOxygendaiCity();
        $phoneInfo = JuhePhoneService::getPhoneInfo($mobile);
        
        if(!empty($phoneInfo))
        {
            $city = isset($phoneInfo['city']) ? $phoneInfo['city'] : '';
           
            if(empty($city))
            {
                $city = isset($phoneInfo['province']) ? $phoneInfo['province'] : '';

            }
        }
      
        if(in_array($city, $arrCity))
        {
            return true;
        }
        return false;
    }
}
