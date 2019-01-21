<?php

namespace App\Services\Core\Oneloan\Niwodai\Miaola;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use App\Services\Core\Oneloan\Dongfang\DongfangConfig\DongfangConfig;
use App\Services\Core\Oneloan\Niwodai\Miaola\Config\MiaolaConfig;
use Illuminate\Support\Facades\Log;

/**
 * 你我贷-秒啦对接
 */
class MiaolaService extends AppService
{

    /**
     * 借款申请接口
     *
     * @param array $params
     * @return mixed|string   false 未注册　　true 已注册
     */
    public static function apply($params = [])
    {
        //链接地址
        $url = MiaolaConfig::REAL_URL;
        $token = MiaolaConfig::getAccessToken();
        //Log::info('token', ['message' => $token, 'code' => 1005]);
        $data = [
            'time' => MiaolaConfig::getMillionTime(),
            'nwd_ext_aid' => MiaolaConfig::ADV_SPACE,
            'phone' => $params['phone'],
            'realName' => $params['realName'],
            'age' => $params['age'],
            'birthTime' => $params['birthTime'],
            'cityName' => $params['cityName'],
            'amount' => $params['amount'],
            'token' => $token,
        ];
        $jsonParams = json_encode($data, JSON_UNESCAPED_UNICODE);
        //整理参数
        $request = [
            'form_params' => [
                'accessCode' => MiaolaConfig::ACCESS_CODE,
                'jsonParam' => $jsonParams,
            ],
        ];
        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $arr = json_decode($result, true);

        return $arr;
    }

}

