<?php

namespace App\Services\Core\Data\Insurance;

use App\Services\AppService;
use App\Helpers\Http\HttpClient;

/**
 * 赠险服务
 * Class ClubService
 */
class InsuranceService extends AppService{

    //保险对接地址
    const INSURANCE_URL = "http://121.196.26.9/insurance/appsvr/life/donate";

    //单例模式
    public static $services;

    public static function i()
    {

        if (!(self::$services instanceof static))
        {
            self::$services = new static();
        }

        return self::$services;
    }

    /**
     * @param $mobile
     * @param $cooperate
     * @return string
     *  生成保险服务
     */
    public static function getInsurance($params = [])
    {
        $url  = self::INSURANCE_URL;
        //post 传值数据
        $request = [
            'form_params' => [
                'name' => $params['realname'],
                'idNo' => $params['certificate_no'],
                'mobile' => $params['mobile'],
                'channel' => $params['channel_num'],
                'remark' => $params['remark'],
                'customerIp' => $params['created_ip'],
            ],
        ];
        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        return $result?json_decode($result,'true'):[];
    }
}