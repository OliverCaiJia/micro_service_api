<?php

namespace App\Listeners\V1;

use App\Events\AppEvent;
use App\Events\V1\UserInsuranceEvent;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Data\Heiniu\HeiniuService;
use App\Services\Core\Tools\Phone\JuhePhoneService;
use App\Services\Core\Tools\Phone\PhoneService;
use App\Strategies\SpreadStrategy;
use Illuminate\Queue\InteractsWithQueue;
use App\Listeners\AppListener;
use App\Models\Chain\Creditcard;
use App\Models\Chain\Creditcard\Apply\DoApplyHandler;
use Illuminate\Support\Facades\Log;
use App\Helpers\Utils;

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

    /**
     * Handle the event.
     *
     * @param  AppEvent  $event
     * @return void
     */
    public function handle(UserInsuranceEvent $event)
    {
        if(!UserSpreadFactory::checkIsSpread($event->data))
        {
            $this->pushInsuranceData($event->data);
        }

    }

    /**
     * 对黑牛返回的数据进行处理
     *
     * @param $data
     */
    public function pushInsuranceData($data)
    {
        $age = Utils::getAge($data['birthday']);
        if($this->checkAge($age))
        {
            if($this->checkCityAgain($data['mobile']))
            {
                //访问
                $res = HeiniuService::insurance($data);
                $params['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
                $params['mobile'] = isset($data['mobile']) ? $data['mobile'] : '';
                $params['id'] = isset($data['log_id']) ? $data['log_id'] : 0;
                $params['type_id'] = isset($data['type_id']) ? $data['type_id'] : 0;
                //对接口进行数据处理
                if(isset($res['error_code']) && $res['error_code'] == 0)
                {
                    // 成功
                    $params['message'] = isset($res['error_msg']) ? $res['error_msg'] : '赠险成功';
                    $params['status'] = 1;
                } else {
                    // 失败
                    $params['message'] = isset($res['error_msg']) ? $res['error_msg'] : '赠险失败';
                    $params['status'] = 0;
                }
                // 更新分发数据状态
                UserSpreadFactory::insertOrUpdateUserSpreadLog($params);
                // 更新产品类型数据
                UserSpreadFactory::updateSpreadTypeTotal(UserSpreadFactory::SPREAD_HEINIU_NID,$params['status']);
            }
        }
    }

    /**
     * 检查年龄
     *
     * @param $age
     * @return bool
     */
    private function checkAge($age)
    {
        if($age >= 25 && $age <= 47)
        {
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
        if(!empty($phoneInfo))
        {
            if(isset($phoneInfo['att']) && !empty($phoneInfo['att']))
            {
                $citys = explode(',', $phoneInfo['att']);
                $city = array_pop($citys);
            }
        }

        if(in_array($city, $arrCity))
        {
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
        $arrCity = SpreadStrategy::getHeiNiuCity();
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
