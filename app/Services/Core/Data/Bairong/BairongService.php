<?php

namespace App\Services\Core\Data\Bairong;

use App\Helpers\Http\HttpClient;
use App\Models\Factory\BairongFactory;
use App\Services\AppService;
use Carbon\Carbon;

class BairongService extends AppService
{
    const BAIRONG_URL = "https://api.100credit.cn";
    const BAIRONG_USERNAME = 'sdzjAPI';
    const BAIRONG_PASSWORD = 'sdzjAPI';
    const BAIRONG_APICODE = '3500003';

    /**
     * 获取查询数据
     *
     * @param $phone
     * @return mixed
     */
    public static function getQueryData($phone)
    {
        $url = self::BAIRONG_URL. '/orihacos/v1/query';
        $data = [
            'meal' => 'Flow_engine',
            'cell' => $phone
        ];
        $jsonData = json_encode($data);
        $checkCode = self::getCheckCode($jsonData);
        $tokenid = self::getTokenId();
        $request = [
            'form_params' => [
                'apiCode' => self::BAIRONG_APICODE,
                'tokenid' => $tokenid,
                'jsonData' => $jsonData,
                'checkCode' => $checkCode
            ]
        ];
        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $arr = json_decode($result, true);

        //为了保险如果返回'100007',对token进行清除
        if(isset($arr['code']) && $arr['code'] == '100007')
        {
            BairongFactory::delCache(BairongFactory::BAIRONG_TOKENID);
        }

        return ($arr['code'] == '600000') ? $arr : [];
    }

    /**
     * 获取tokenid
     *
     * @return string
     */
    public static function getTokenId()
    {
        $tokenid = BairongFactory::getCache(BairongFactory::BAIRONG_TOKENID);
        if(empty($tokenid))
        {
            $url = self::BAIRONG_URL. "/bankServer2/user/login.action";
            $request = [
                'form_params' => [
                    'userName' => self::BAIRONG_USERNAME,
                    'password' => self::BAIRONG_PASSWORD,
                    'apiCode' => self::BAIRONG_APICODE
                ],
            ];
            $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
            $result = $response->getBody()->getContents();
            $arr = json_decode($result, true);
            $tokenid = ($arr['code'] == '00') ? $arr['tokenid'] : "";
            //把获取的token放入redis中,过期时间50分钟
            if(!empty($tokenid))
            {
                BairongFactory::setCache(BairongFactory::BAIRONG_TOKENID, $tokenid, Carbon::now()->addMinutes(50));
            }
        }
        return $tokenid;
    }

    /**
     * 校验码
     *
     * @param string $jsonData
     * @return string
     */
    public static function getCheckCode($jsonData = "")
    {
        return md5($jsonData.md5(self::BAIRONG_APICODE.self::getTokenId()));
    }
}