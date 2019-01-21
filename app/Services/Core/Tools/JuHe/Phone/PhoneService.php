<?php

namespace App\Services\Core\Tools\JuHe\Phone;

use App\Services\Core\Tools\ToolsService;
use App\Helpers\Http\HttpClient;

class PhoneService extends ToolsService
{
    //地址
    const PHONE_API_URL = 'http://api.k780.com:88/';

    /**
     * 获取手机信息地址信息
     *
     * @param $phone
     * @return mixed|string
     */
    public static function getPhoneInfo($phone)
    {
        $url = self::PHONE_API_URL."?app=phone.get&phone=$phone&appkey=10003&sign=b59bc3ef6191eb9dsfsaf9f2a4&format=json";
        $response = HttpClient::i()->request('GET', $url);
        $result = $response->getBody()->getContents();
        $result =  json_decode($result, true);

        return $result ? $result['result'] : [];
    }


}
