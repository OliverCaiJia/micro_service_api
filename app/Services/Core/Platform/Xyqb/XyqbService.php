<?php

namespace App\Services\Core\Platform\Xyqb;

use App\Helpers\Http\HttpClient;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\PlatformService;

/**
 * 信用钱包
 */
class XyqbService extends PlatformService
{
    //测试线地址
    //const URL = 'http://61.50.125.14:9001/app/login';
    //正式线地址
    const URL = 'http://auth.xyqb.com/app/login';

    /**
     * @param $page
     * @param $mobile
     * @param $cooperate
     * @return string
     * 量化派 —— 信用钱包 对接地址
     */
    public static function fetchQuantgroupUrl($datas)
    {
        $mobile = $datas['user']['mobile']; //手机号
        $page = $datas['page']; //地址

        $vargs = http_build_query([
            'phoneNo' => $mobile, // 手机号码
            'registerFrom' => '257'    //请求来源
        ]);
        $url = XyqbService::URL . '?' . $vargs;

        $promise = HttpClient::i()->request('GET', $url);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);
        //dd($result);

        if ($result['code'] == '0000' && isset($result['data']) && !empty($result['data'])) {
            $page = $page . '&token=' . $result['data']['token'];
        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $datas['user']['mobile'];
        $datas['channel_no'] = '257';
        $datas['apply_url'] = $page;
        $datas['feedback_message'] = isset($result['msg']) ? $result['msg'] : '';
        $datas['is_new_user'] = 0;
        //对接平台返回对接信息记流水
        $log = OauthFactory::createDataProductAccessLog($datas);

        return $page ? $page : '';
    }

}
