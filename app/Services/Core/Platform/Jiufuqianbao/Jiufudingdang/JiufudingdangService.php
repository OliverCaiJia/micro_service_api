<?php

namespace App\Services\Core\Platform\Jiufuqianbao\Jiufudingdang;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\PlatformService;

/**
 * Class JiufudingdangService
 * @package App\Services\Core\Platform\Jiufuqianbao\Jiufudingdang
 * 玖富钱包 —— 玖富叮当贷
 */
class JiufudingdangService extends PlatformService
{
    //渠道
    const PROID = 'eb05f4freeceaacfsdsf4aee9csdfc4b588f572b954';
    //测试环境地址sdf
    //const URL = 'https://cubeapitest.9fbank.com/cubeLogin/unionLoginSDZJ';
    //正式线环境地址
    const URL = 'https://cubeapi.9fbank.com/cubeLogin/unionLoginSDZJ';

    /**
     * @param $mobile
     * @param $page
     * @return string
     * 玖富叮当贷 对接地址
     */
    public static function fetchJiufudingdangUrl($datas)
    {
        $mobile = $datas['user']['mobile']; //手机号
        $page = $datas['page']; //地址

        //判断url中是否含有参数proId
        $urlArray = parse_url($page);
        $urlParams = Utils::convertUrlQuery(isset($urlArray['query']) ? $urlArray['query'] : '');
        $proId = self::PROID;

        //加密后的渠道
        $encryptProId = RsaUtil::i()->rsaEncrypt($proId);
        //加密后的手机号
        $encryptMobile = RsaUtil::i()->rsaEncrypt($mobile);
        //post请求url地址
        $vargs = http_build_query([
            'proIdEncod' => $encryptProId,
            'mobileEncod' => $encryptMobile,
        ]);
        $url = self::URL . '?' . $vargs;

        //post请求传值参数
        $request = [
            'form_params' => [
                'proIdEncod' => $encryptProId,
                'mobileEncod' => $encryptMobile,
            ],
        ];

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);
        //dd($result);

        if (isset($result['data']) && !empty($result['data'])) {
            $page = $result['data']['linkUrl'];
        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $datas['user']['mobile'];
        $datas['channel_no'] = 'SDZJ';
        $datas['apply_url'] = $page;
        $datas['feedback_message'] = isset($result['message']) ? $result['message'] : '';
        $datas['is_new_user'] = 0;
        //对接平台返回对接信息记流水
        $log = OauthFactory::createDataProductAccessLog($datas);

        return $page ? $page : '';
    }

}