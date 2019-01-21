<?php

namespace App\Services\Core\Data\Hengchang;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\Core\Data\Hengchang\Config\HengchangConfig;
use App\Services\AppService;

/**
 * 恒昌 —— 接口对接Service
 * Class HengchangService
 * @package App\Services\Core\Data\Hengchang
 */
class HengchangService extends AppService
{
    /**
     * 恒昌 —— 接口对接Service
     * @param $datas
     */
    public static function spread($datas)
    {
        $request = [
            'form_params' => [
                'name' => $datas['name'],
                'mobile' => $datas['mobile'],
                'city' => $datas['city'],
                'source' => $datas['source'],
                'ip' => $datas['ip']
            ]
        ];
        if (isset($datas['baodan_is'])) {
            $request['form_params']['baodan_is'] = '有';
        }
        if (isset($datas['house'])) {
            $request['form_params']['house'] = '有';
        }
        if (isset($datas['car'])) {
            $request['form_params']['car'] = '有';
        }

        $url = ZhudaiwangConfig::URL;

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);
        return $result;
    }
}