<?php

namespace App\Services\Core\Oneloan\Zhongtengxin;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Services\AppService;
use App\Services\Core\Oneloan\Zhongtengxin\Config\ZhongtengxinConfig;

/**
 * 中腾信对接
 * Class ZhongtengxinService
 * @package App\Services\Core\Platform\Zhongtengxin
 */
class ZhongtengxinService extends AppService
{
    /**
     * 中腾信推广
     * @param $params
     * @return mixed
     */
    public static function spread($params = [])
    {
        //地址渠道channel ?callback=?&channel=
        $vargs = http_build_query([
            'channel' => ZhongtengxinConfig::CHANNEL,
        ]);

        //参数处理  北京市 去除 '市'
        $params['city'] = mb_strpos($params['city'], '市') ? rtrim($params['city'], '市') : $params['city'];

        //请求参数
        $request = [
            'form_params' => [
                'tokenId' => $params['user_id'] . '',   //保证业务唯一
                'name' => $params['name'],  //用户名
                'telephone' => $params['mobile'], //电话
                'city' => $params['city'],  //城市
                'vocation' => ZhongtengxinConfig::getOccupation($params['occupation']), //职业身份
                'income' => ZhongtengxinConfig::getSalary($params['salary']),
            ],
        ];
        //请求地址
        $url = ZhongtengxinConfig::URL . $vargs;
        //SLogger::getStream()->info('zhongtengxin', ['url' => $url]);
        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        //返回空值
        if (empty($result)) {
            return [];
        }

        //返回格式 ?({"result":"非白名单请求,非法"})，进行处理
        $result = mb_substr($result, 2);
        $result = rtrim($result, ')');
        $arr = json_decode($result, true);

        return $arr;
    }
}