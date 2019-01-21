<?php

namespace App\Services\Core\Platform\Xinerfu\Xianjindai;

use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\PlatformService;
use App\Services\Core\Platform\Xinerfu\Xianjindai\RsaUtil;
use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;

/**
 * 信而富
 */
class XianjindaiService extends PlatformService
{
    // 通用参数
//    const SALESMAN_NO = 'JKTZNJ0112';
//    const AGENT_NO = 'JKTZNJ0112_20170119SDCSD001';
    // 测试环境用参数
//    const SYSTEM_CODE = '103';
//    const url = 'https://promotion-uat.crfchina.com/promotion/interface/imm3';
//    const downUrl = 'https://promotion-uat.crfchina.com/kaniu/success.html';

    // 正式参数
    const SALESMAN_NO = 'JKTDST082';
    const AGENT_NO = 'JKTZHZ0054_20170RETTJZJWL002';
    const SYSTEM_CODE = '22';
    const url = 'https://promotion.crfchina.com/promotion/face/imm3';
    const downUrl = 'https://promotion.crfchina.com/kaniu/suc.html';
    const staticUrl = 'https://promotion.crfchina.com/Market/index.html';

    // 获取信而富的url
    public static function fetchXinerfuUrl($datas)
    {
        $params = [
            'salesmanNo' => self::SALESMAN_NO, // 销售员编号 [对方]
            'agentNo' => self::AGENT_NO,       // 代理人编号 [对方]
            'source' => 'imm3',                // 渠道号 [对方]
            'systemCode' => self::SYSTEM_CODE, // 系统编号 [必填]
        ];

        // 公钥加密
        $username = $datas['user']['username'];
        $mobile = $datas['user']['mobile'];
        $sign = RsaUtil::i()->rsaEncrypt(self::getSign($mobile, $username));
        $params['phone'] = $mobile;
        $params['name'] = $username;
        $params['sign'] = $sign;

        $args = http_build_query($params);
        $url = self::url . '?' . $args;

        // get请求访问
        $response = HttpClient::i(['verify' => false])->request('GET', $url);
        $result = $response->getBody()->getContents();
        $result = json_decode($result, true);
        //dd($result);

        $page = '';
        if (isset($result['code'])) {
            if ($result['code']) {
                $page = self::downUrl;
            } else {
                $param = [
                    'c' => '',
                    's' => 'imm3',
                    'salesmanNo' => $params['salesmanNo'],
                    'agentNo' => $params['agentNo'],
                ];
                $page = self::staticUrl . '?' . http_build_query($param);
            }
        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $datas['user']['mobile'];
        $datas['channel_no'] = 'imm3';
        $datas['apply_url'] = $page;
        $datas['feedback_message'] = '';
        $datas['is_new_user'] = 0;
        //对接平台返回对接信息记流水
        $log = OauthFactory::createDataProductAccessLog($datas);

        return $page;
    }

    /** 获取sign
     * @param $mobile
     * @param $username
     * @return string
     */
    private static function getSign($mobile, $username)
    {
        $signData = [
            'phone' => $mobile,                // 手机号　[用户]
            'salesmanNo' => self::SALESMAN_NO, // 销售员编号 [对方]
            'agentNo' => self::AGENT_NO,       // 代理人编号 [对方]
            'name' => $username,               // 姓名   [用户]
            'source' => 'imm3',                // 渠道号 [对方]
        ];
        ksort($signData);
        $signText = '';
        foreach ($signData as $key => $val) {
            $signText = $signText . $key . '=' . $val;
            if (next($signData)) {
                $signText = $signText . '&';
            }
        }

        $encode = urlencode($signText);
        return $encode;
    }
}
