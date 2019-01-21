<?php

namespace App\Services\Core\Oneloan\Youli;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use App\Services\Core\Oneloan\Youli\YouliConfig\YouliConfig;

/**
 * 有利对接
 */
class YouliService extends AppService
{
    /**
     * 注册接口
     *
     * @param array $params
     * @return mixed
     */
    public static function register($params = [])
    {
        //请求url
        $url = YouliConfig::URL .'?aid=' . YouliConfig::CHANNEL_ID;
        //参数都是必须的
        $request = [
            'form_params' => [
                'name' => $params['name'],
                'mobile' => $params['mobile'],
                'idCard' => $params['idCard'],
                'loanAmount' => $params['loanAmount'],
                'income' => $params['income'],
                'ishouse' => $params['ishouse'],
                'iscar' => $params['iscar'],
            ],
        ];

        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $arr = json_decode($result, true);

        return $arr;
    }
}

