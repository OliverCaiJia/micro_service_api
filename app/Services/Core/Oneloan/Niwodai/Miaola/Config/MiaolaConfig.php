<?php

namespace App\Services\Core\Oneloan\jdt\Miaola\Config;

use App\Helpers\Http\HttpClient;
use App\Models\Cache\CommonCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 *  你我贷-秒啦配置
 */
class MiaolaConfig
{
    //正式环境
    const FORMAL_URL = 'https://api.jdt.com/interface/callHttpInterfaces.do';
    //测试环境
    const TEST_URL = 'http://api.jdt.org/interface/callHttpInterfaces.do';
    //对应真实环境
    const REAL_URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    //商户代码
    const MERCHANTS_CODE = '2381';
    //验签接口码
    const VER_ACCESS_CODE = PRODUCTION_ENV ? 'a7ded736-5ba6-43e9-803338-20751f120a26' : '8f9e4b2e-9d33e3-4e24-b24b-430f4d93f4ae';
    //验签商户用户名
    const VER_APP_ID = PRODUCTION_ENV ? 'jdtBD' : 'APItest';
    //验签商户密码
    const VER_APP_KEY = PRODUCTION_ENV ? '782594f6120cc92e4fcf8ac405b' : '0f2bea71c4fa6544b06';

    //贷款申请接口码
    const ACCESS_CODE = PRODUCTION_ENV ? 'e7e5c497-0cba-40rrae-93aa-c41f6c10b79d' : '1e5c04b6-21rrbc-4781-8710-c6501d26a6aa';
    //广告申请ID
    const ADV_SPACE = PRODUCTION_ENV ? '502016235622111' : '50201604446001';

    /**
     * 获取accessToken
     *
     * @return mixed
     */
    public static function getAccessToken()
    {
        $token = CommonCache::getCache(CommonCache::jdt_TOKEN);
        if(empty($token))
        {
            $url = MiaolaConfig::REAL_URL;
            $params = [
                'appId' => MiaolaConfig::VER_APP_ID,
                'appKey' => MiaolaConfig::VER_APP_KEY,
            ];
            $jsonParams = json_encode($params, JSON_UNESCAPED_UNICODE);

            $request = [
                'form_params' => [
                    'accessCode' => MiaolaConfig::VER_ACCESS_CODE,
                    'jsonParam' => $jsonParams,
                ],
            ];
            $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
            $result = $response->getBody()->getContents();
            $arr = json_decode($result, true);

            if(isset($arr['success']) && $arr['success'] == 1)
            {
                if(isset($arr['data']['accessToken']) && !empty($arr['data']['accessToken']))
                {
                    CommonCache::setCache(CommonCache::jdt_TOKEN, $arr['data']['accessToken'], Carbon::now()->addMinutes(100));
                    $token = $arr['data']['accessToken'];
                }
            }
        }

        return $token;
    }

    /**
     * 获取毫秒时间戳
     *
     * @return string
     */
    public static function getMillionTime()
    {
        //获取毫秒时间
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec * 1000);
        $millisecond = str_pad($msec, 3, '0', STR_PAD_RIGHT);
        $milliTime = date("YmdHis") . $millisecond;

        return $milliTime;
    }

    /**
     * 处理一下参数
     *
     * @param $params
     * @return array
     */
    public static function getParams($params)
    {
        $arr = [
            'phone' => $params['mobile'],
            'realName' => $params['name'],
            'age' => $params['age'],
            'birthTime' => date('Y-m-d', strtotime($params['birthday'])),
            'cityName' => mb_substr($params['cityname'], 0, -1),
            'amount' => $params['money'],
        ];

        return $arr;
    }

}
