<?php

namespace App\Services\Core\Validator\IP\Taobao;

use App\Helpers\Http\HttpClient;
use App\Services\Core\Validator\ValidatorService;

/**
 * 淘宝手机号
 *
 * Class TaobaoPhoneService
 * @package App\Services\Core\Validator\TaoBao
 */
class TaobaoPhoneService extends ValidatorService
{
    //地址
    const TAOBAO_PHONE_URL_API = 'https://tcc.taobao.com/cc/json/mobile_tel_segment.htm';

    /**
     * 获取手机号的归属地
     *
     * @param array $data
     * @return mixed
     * 返回样例
     * [
        'mts' => '1310460',
        'province' => '黑龙江',
        'catName' => '中国联通',
        'telString' => '13104607054',
        'areaVid' => '30496',
        'ispVid' => '137815084',
        'carrier' => '黑龙江联通'
       ]
     */
    public static function getPhoneAddress($data = [])
    {
        $url = self::TAOBAO_PHONE_URL_API;

        $request = [
            'query' => [
                'tel' => $data['mobile'],
                't' => time(),
            ]
        ];
        $response = HttpClient::i()->request('GET', $url, $request);
        $result = $response->getBody()->getContents();

        //将不是标准的json字符串进行解析
        $result = trim(explode('=',$result)[1]);
        $result = iconv('gbk','utf-8', $result);
        $result = str_replace("'",'"', $result);
        $result = preg_replace('/(\w+):/is', '"$1":', $result);

        $res = json_decode($result, true);

        return $res;
    }

}
