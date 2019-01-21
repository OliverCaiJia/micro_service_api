<?php

namespace App\Services\Core\Tools\TaoBao\Ip;

use App\Helpers\Http\HttpClient;
use App\Services\Core\Tools\ToolsService;
use Mockery\Exception;

/**
 * 聚合IP用途
 * Class JuHeIpService
 * @package App\Services\Core\Tools\JuHe\Ip
 */
class IpService extends ToolsService
{
    //淘宝接口地址
//    const API_URL = 'http://ip.taobao.com/service/getIpInfo.php';
    const API_URL = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php';


    public static function fetchAddressByIp($ip)
    {
        //请求接口地址
        $url = self::API_URL;

        $params = array(
            'format' => 'json',
            "ip" => $ip,//需要查询的IP地址或域名
        );
        $paramstring = $url . '?' . http_build_query($params);
        //请求淘宝地址
        $response = HttpClient::i(['timeout' => 3])->request('GET', $paramstring);
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);

        return $result ? $result : [];

    }
}