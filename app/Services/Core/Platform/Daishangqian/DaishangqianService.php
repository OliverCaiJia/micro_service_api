<?php

namespace App\Services\Core\Platform\Daishangqian;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\PlatformService;
use App\Strategies\OauthStrategy;

/**
 * 贷上钱
 */
class DaishangqianService extends PlatformService
{
    //渠道编号：贷上钱方为机构方生成的唯一编号
    //速贷之家-3 58db2d16440d  ，速贷之家-2 58db2d4e7fd9	  速贷之家-1 58db2c35dafa 线上三条渠道编号
    const CHANNERL = '58db2c35dafa';
    //密钥：贷上钱方为机构生成的一个用于签名及加解密的字符串  每一个渠道号对应一个秘钥
    //速贷之家-1的秘钥
    const SECRET = '601c2a83eb35ae3a';
    //测试环境地址
    //const URL = 'http://paydayloan.fond.io/asset/third/register';
    //正视环境地址
    const URL = 'https://api.daishangqian.com/asset/third/register';

    /**
     * @param $mobile
     * @param $cooperate
     * @return string
     * 贷上钱 —— 贷上钱对接地址
     */
    public static function fetchDaishangqianUrl($datas)
    {
        $mobile = $datas['user']['mobile']; //手机号
        $page = $datas['page']; //地址

        $bizData = [
            'mobile' => $mobile,
        ];

        //1.密钥：贷上钱方为机构生成的一个用于签名及加解密的字符串
        //2.然后对密钥进行md5，截取前16个字符，作为AES算法的密码password和初始变量iv
        $secret = DaishangqianService::SECRET;
        $iv = mb_substr(md5($secret), 0, 16, 'utf-8');

        //1.接口业务数据【手机号】，需要先json_encode，再加密处理
        //2.对json_encode后的bizData进行加密处理，然后将结果进行base64_encode处理，得到加密后的值
        $encodeBizData = json_encode($bizData);
        $aesBizData = Utils::AesEncrypt($encodeBizData, $iv, $iv);

        //1、对需要签名的参数，根据参数名进行字典排序
        //2、将排序后的参数数组，生成key1=value1&key2=value2格式
        //3、把密钥拼接在第2步生成的字符串后面，然后对连接后的字符串进行md5操作，得到签名值
        $channel = DaishangqianService::CHANNERL;
        $signData = [
            'channel' => $channel,
            'bizData' => $aesBizData,
        ];
        ksort($signData);
        $signText = '';
        foreach ($signData as $key => $val) {
            $signText = $signText . '&' . $key . '=' . $val;
        }
        $signStr = mb_substr($signText . $secret, 1);
        $sign = md5($signStr);

        //post 传值数据
        $request = [
            'form_params' => [
                'channel' => $channel,
                'bizData' => $aesBizData,
                'sign' => $sign,
            ],
        ];
        $url = DaishangqianService::URL;

        //发送请求
        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);
        //dd($result);

        if ($result['code'] == 0 && isset($result['data']) && !empty($result['data'])) {
            $page = $result['data']['skipUrl'];
        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $datas['user']['mobile'];
        $datas['channel_no'] = self::CHANNERL;
        $datas['apply_url'] = $page;
        $datas['feedback_message'] = isset($result['message']) ? $result['message'] : '';
        $datas['is_new_user'] = isset($result['data']['isNewUser']) ? OauthStrategy::formatDaishangqianIsNewUser($result['data']['isNewUser']) : 0;
        //对接平台返回对接信息记流水
        $log = OauthFactory::createDataProductAccessLog($datas);

        return $page ? $page : '';

    }


}
