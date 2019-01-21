<?php

namespace App\Services\Core\Platform\Renxinyong\Renxinyong;

use App\Helpers\Http\HttpClient;
use App\Services\Core\Platform\PlatformService;
use App\Services\Core\Platform\Renxinyong\Renxinyong\Config\RenxinyongConfig;
use App\Services\Core\Platform\Renxinyong\Renxinyong\Util\RsaUtil;

/**
 * 任性用对接
 * Class RenxinyongService
 * @package APP\Services\Core\Platform\Renxinyong
 */
class RenxinyongService extends PlatformService
{
    /**
     *
     * @param array $datas
     * @return mixed
     */
    public static function fetchRenxinyongUrl($datas = [])
    {
        //原始地址
        $page = $datas['page'];
        //对接参数
        $params = [
            'phoneNo' => $datas['user']['mobile'], // 手机号
            'channelCode' => RenxinyongConfig::CHANNEL_CODE, // 渠道号 [对方]
        ];

        //转化json
        $jsonParams = json_encode($params);
        //公钥加密
        $rsaParams = RsaUtil::i()->rsaEncrypt($jsonParams);

        //请求数据
        $requestData = [
            'form_params' => [
                'data' => $rsaParams,
            ],
        ];

        // 获取url
        $url = RenxinyongConfig::URL;

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $requestData);
        $result = $promise->getBody()->getContents();
        $resultObj = json_decode($result, true);

        //对接地址
        if (isset($resultObj['code']) && $resultObj['code'] == '0000') {
            $page = isset($resultObj['result']['url']) ? $resultObj['result']['url'] : '';
        }

        return $page;
    }
}