<?php

namespace App\Services\Core\Oneloan\Niwodai;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use App\Services\Core\Oneloan\Dongfang\DongfangConfig\DongfangConfig;
use App\Services\Core\Oneloan\Niwodai\NiwodaiConfig\NiwodaiConfig;
use Illuminate\Support\Facades\Log;

/**
 * 你我贷对接
 */
class NiwodaiService extends AppService
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
        $url = NiwodaiConfig::REAL_URL;
        $token = NiwodaiConfig::getAccessToken();
        Log::info('token', ['message' => $token, 'code' => 1005]);
        $data = [
            'time' => NiwodaiConfig::getMillionTime(),
            'nwd_ext_aid' => NiwodaiConfig::ADV_SPACE,
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
                'accessCode' => NiwodaiConfig::ACCESS_CODE,
                'jsonParam' => $jsonParams,
            ],
        ];
        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $arr = json_decode($result, true);

        return $arr;
    }

}

