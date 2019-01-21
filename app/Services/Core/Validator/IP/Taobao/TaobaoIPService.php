<?php

namespace App\Services\Core\Validator\IP\Taobao;

use App\Helpers\Http\HttpClient;
use App\Services\Core\Validator\ValidatorService;

/**
 * 淘宝ip
 *
 * Class TaobaoIPService
 * @package App\Services\Core\Validator\IP\Taobao
 */
class TaobaoIPService extends ValidatorService
{
    //地址
    const TAOBAO_IP_URL_API = 'http://ip.taobao.com/service/getIpInfo.php';

    /**
     * 获取ip的归属地
     *
     * @param array $data
     * @return mixed
     * 返回样例
     * [
        'ip' => '112.17.235.255',
        'country' => '中国',
        'area' => '',
        'region' => '浙江',
        'city' => '杭州',
        'county' => 'XX',
        'isp' => '移动',
        'country_id' => 'CN',
        'area_id' => '',
        'region_id' => '330000',
        'city_id' => '330100',
        'county_id' => 'xx',
        'isp_id' => '100025'
       ]
     */
    public static function getIpAddress($data = [])
    {
        $url = self::TAOBAO_IP_URL_API;

        $request = [
            'query' => [
                'ip' => $data['ip'],
            ]
        ];
        $response = HttpClient::i()->request('GET', $url, $request);
        $result = $response->getBody()->getContents();

        $res = json_decode($result, true);

        return $res ? $res['data'] : [];
    }
}
