<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Events\V1\UserSpreadCountEvent;
use App\Events\V1\UserSpreadEvent;
use App\Models\Factory\UserSpreadFactory;
use App\Models\Orm\UserSpread;
use App\Models\Orm\UserSpreadType;
use App\Services\Core\Data\Zhudaiwang\ZhudaiwangService;
use App\Services\Core\Tools\Phone\JuhePhoneService;
use App\Services\Core\Tools\Phone\PhoneService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Models\Chain\Creditcard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * @param  AppEvent  $event
     * @return void
     */
    public function handle(UserSpreadEvent $event)
    {
        $type_nid = UserSpreadFactory::SPREAD_ZHUDAIWANG_NID;//'spread_loan';
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
                $this->pushLoanData($event->data, $spread);
            }
        }
    }

    /**
     * 数据处理
     *
     * @param $data
     * @return bool
     */
    public function pushLoanData($data, $spread)
    {
        $typeNid = UserSpreadFactory::SPREAD_ZHUDAIWANG_NID;//'spread_loan';
        $limit = SpreadStrategy::getPushProductLimit($typeNid, $data['mobile']);
        if($limit)
        {
            return true;
        }

        if ($data['type_id'] != 0)
        {
            $age = intval(date('Y', time())) - intval(date('Y', strtotime($spread->birthday)));

            if($this->checkAge($age))
            {
               if($this->checkCondition($data))
               {
                    if($this->checkCityAgain($spread['city'], $data['mobile']))
                    {
                        if ($data['total'] < $data['limit'])
                        {
                            // 创建流水
                            $spread['age'] = $age;
                            $spread['type_id'] = $data['type_id'];
                            $spread['id'] = 0;
                            unset($spread['status']);
                            $params['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
                            //  推送助贷网
                            $res = ZhudaiwangService::spread($spread);
                            //处理结果
                            $params['type_id'] = $data['type_id'];
                            $params['mobile'] = $data['mobile'];
                            $params['message'] = ZhudaiwangService::getMessage(intval($res));
                            $params['result'] = $res;
                            if(intval($res) > 1000000)
                            {
                                $params['status'] = 1;
                            } else {
                                $params['status'] = 0;
                            }
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
        if($age >= 25 && $age <= 55)
        {
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
        if($data['has_insurance'] == 1 or in_array($data['car_info'], ['001', '002']) or in_array($data['house_info'], ['001', '002']))
        {
            return true;
        }

        return false;
    }

    /**
     * 检查城市
     *
     * @param $city
     * @return bool
     */
    private function checkCity($city)
    {
        if(in_array($city, ['深圳市', '上海市', '北京市', '广州市', '杭州市']))
        {
            return true;
        }

        return false;
    }

    /**
     * 再次检查城市
     *
     * @param string  $wCity 手写的城市
     * @param string $mobile 手机号
     * @return bool
     */
    private function checkReCity($wCity, $mobile)
    {
        return true;
        $arr = ['深圳市', '上海市', '北京市', '广州市', '杭州市'];
        $city = '';
        $phoneInfo = PhoneService::getPhoneInfo($mobile);
        if(!empty($phoneInfo))
        {
            if(isset($phoneInfo['style_citynm']) && !empty($phoneInfo['style_citynm']))
            {
                $citys = explode(',', $phoneInfo['style_citynm']);
                $city = array_pop($citys);
            }
        }

        if(in_array($city, $arr) || in_array($wCity, $arr))
        {
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
        if(!empty($phoneInfo))
        {
            $city = isset($phoneInfo['city']) ? $phoneInfo['city'] : '';
            if(empty($city))
            {
                $city = isset($phoneInfo['province']) ? $phoneInfo['province'] : '';
            }
        }

        if(in_array($city, $arr) || in_array($wwCity, $arr))
        {
            return true;
        }

        return false;
    }

}
