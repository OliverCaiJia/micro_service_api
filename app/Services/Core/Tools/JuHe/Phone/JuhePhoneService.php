<?php

namespace App\Services\Core\Tools\JuHe\Phone;

use App\Services\Core\Tools\ToolsService;
use App\Helpers\Http\HttpClient;

class JuhePhoneService extends ToolsService
{
    //地址
    const PHONE_API_URL = 'http://apis.juhe.cn/mobile/get';
    //key
    const PHONE_API_KEY = '7c02c644a1dc55dsfbde68aebsdf';
    /**
     * 获取手机信息地址信息
     *
     * @param $phone
     * @return mixed|string
     */
    public static function getPhoneInfo($phone)
    {
        $url = self::PHONE_API_URL."?phone=$phone&key=". self::PHONE_API_KEY;
        $response = HttpClient::i()->request('GET', $url);
        $result = $response->getBody()->getContents();
        $result =  json_decode($result, true);

        return $result ? $result['result'] : [];
    }


}
