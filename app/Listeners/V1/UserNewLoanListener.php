<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Events\V1\UserSpreadEvent;
use App\Events\V1\UserSpreadCountEvent;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Orm\UserSpread;
use App\Models\Orm\UserSpreadType;
use App\Services\Core\Data\Xinyidai\XinyidaiService;
use App\Services\Core\Tools\Phone\JuhePhoneService;
use App\Services\Core\Tools\Phone\PhoneService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Models\Chain\Creditcard;
use App\Helpers\Utils;
use Log;

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
     * @param  AppEvent  $event
     * @return void
     */
    public function handle(UserSpreadEvent $event)
    {
        $type_nid = UserSpreadFactory::SPREAD_XINYIDAI_NID;
        $type = UserSpreadFactory::getType($type_nid);
        if(!empty($type))
        {
            $event->data['type_id'] = $type ? $type->id : 0;
            $event->data['limit'] = $type ? $type->limit : 0;
            $event->data['total'] = $type ? $type->total : 0;

            // 插入用户数据
            UserSpreadFactory::createOrUpdateUserSpread($event->data);

            // 推广统计
            $spread = UserSpreadFactory::getSpread($event->data['mobile']);
            $spread['type_id'] = $event->data['type_id'];
            event(new UserSpreadCountEvent($spread->toArray()));

            if(!UserSpreadFactory::checkIsSpread($event->data))
            {
                $this->pushNewLoanData($event->data, $spread);
            }
        }
    }

    /**
     * 处理新一贷数据
     *
     * @param $data
     * @return bool
     */
    public function pushNewLoanData($data, $spread)
    {
        $typeNid = UserSpreadFactory::SPREAD_XINYIDAI_NID;//'spread_newloan';
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
            $cityCode = $this->checkCityAgain($data['mobile']);//SpreadStrategy::getCityCode($spread->city);
            //年龄
            if($this->checkAge($age))
            {
                $age = '23-55岁';

                if ($this->checkHouse($spread->house_info)) {
                    //用户条件
                    if($this->checkCondition($data))
                    {
                        //筛选城市
                        if($cityCode > 0)
                        {
                            if($data['total'] < $data['limit'])
                            {
                                // 创建流水
                                $spread['type_id'] = $data['type_id'];
                                $spread['age'] = $age;
                                $spread['city_code'] = $cityCode;
                                $spread['id'] = 0;
                                unset($spread['status']);
                                $params['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);

                                //  推送service
                                $res = XinyidaiService::spread($spread);
                                if (isset($res['responseCode'])) {
                                    if ($res['responseCode'] == '000000') {
                                        $params['message'] = '操作成功';
                                        $params['status'] = 1;
                                    } else {
                                        $params['message'] = '操作失败';
                                        $params['status'] = 0;
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
    }

    /**
     * 有房贷推送
     * @param string $house
     * @return bool
     */
    private function checkHouse($house = '') {
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
        //有保险　　　　　　　　　　　　　　　　有信用卡                         有车　　　　　　　　　　　　　　　　　　　　　　　　　　有房　　　　　　　　　　　　　　　　　                 有公积金
        if($data['has_insurance'] == 1 or $data['hasCreditCard'] == 1 or in_array($data['car_info'], ['001', '002']) or in_array($data['house_info'], ['001', '002']) or in_array($data['accumulation_fund'], ['001', '002']))
        {
            return true;
        }

        return false;
    }

    /**
     * 判断城市
     *
     * @param $mobile
     * @return bool
     */
    private function checkCity($mobile)
    {
        return true;
        $city = '';
        $arrCity = SpreadStrategy::setCityCode();
        $phoneInfo = PhoneService::getPhoneInfo($mobile);
        if(!empty($phoneInfo))
        {
            if(isset($phoneInfo['att']) && !empty($phoneInfo['att']))
            {
                $citys = explode(',', $phoneInfo['att']);
                $city = array_pop($citys);
            }
        }

        foreach ($arrCity as $key => $code)
        {
            if(strpos($city, $key) !== false)
            {
                return $code;
            }
        }

        return -1;
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
        $arrCity = SpreadStrategy::setCityCode();
        $phoneInfo = JuhePhoneService::getPhoneInfo($mobile);
        if(!empty($phoneInfo))
        {
            $city = isset($phoneInfo['city']) ? $phoneInfo['city'] : '';
            if(empty($city))
            {
                $city = isset($phoneInfo['province']) ? $phoneInfo['province'] : '';
            }
        }

        foreach ($arrCity as $key => $code)
        {
            if(strpos($city, $key) !== false)
            {
                return $code;
            }
        }

        return -1;
    }
}
