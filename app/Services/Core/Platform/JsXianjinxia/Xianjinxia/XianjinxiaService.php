<?php

namespace App\Services\Core\Platform\JsXianjinxia\Xianjinxia;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\PlatformService;
use App\Services\Core\Platform\JsXianjinxia\Xianjinxia\RsaUtil;
use App\Strategies\OauthStrategy;

/**
 * Class XianjinxiaService
 * @package App\Services\Core\Platform\JsXianjinxia\Xianjinxia
 * 极速现金侠 —— 现金侠
 */
class XianjinxiaService extends PlatformService
{
    //测试线秘钥
    //const SECRET_KEY = '78A22643dgf4E20134593A';
    //正式线秘钥
    const SECRET_KEY = 'A15992Fdfsgs5E634354B3AA';

    //测试线渠道号
    //const CHANNEL_NO = '109837';
    //正式线渠道号 可以使用的渠道号 65 66 67
    const CHANNEL_NO = '65';

    //测试线地址
    //const URL = 'http://116.62.60.23/xjxCreditApplyInfo';
    //正式线地址
    const URL = 'http://channel.xianjinxia.com/xjxCreditApplyInfo';

    /**
     * @param $datas
     * @return string
     * 现金侠对接地址
     */
    public static function fetchXianjinxiaUrl($datas)
    {
        $mobile = $datas['user']['mobile'];
        $username = $datas['user']['username'];
        $page = $datas['page'];

        //从url中获取的参数值
        $urlArray = parse_url($page);
        $params = Utils::convertUrlQuery(isset($urlArray['query']) ? $urlArray['query'] : '');
        //key值
        $secret = isset($params['secret_key']) ? $params['secret_key'] : self::SECRET_KEY;
        //渠道号
        $channelNo = isset($params['channel_no']) ? $params['channel_no'] : self::CHANNEL_NO;
        $data = [
            'mobilephone' => $mobile,
            'channel_no' => $channelNo,
            'name' => $username,
        ];

        //获取iv，iv值为key值的前16个字节,进行aes加密
        $iv = mb_substr($secret, 0, 16, 'utf-8');
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $encryptData = RsaUtil::i()->AesEncrypt($jsonData, $iv, $iv);
        //签名
        $sign = RsaUtil::i()->fetchSign($encryptData, $secret);

        //请求参数
        $request = [
            'form_params' => [
                'data' => $encryptData,
                'sign' => $sign,
            ],
        ];
        //请求地址
        $url = self::URL;

        //发送请求
        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);
        //dd($result);
        //返回值
        if (isset($result['code']) && $result['code'] == 0 && isset($result['apply_url']) && !empty($result['apply_url'])) {
            $page = $result['apply_url'];
        }

        //对接平台返回用户信息进行处理
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $datas['user']['mobile'];
        $datas['channel_no'] = self::CHANNEL_NO;
        $datas['apply_url'] = $page;
        $datas['feedback_message'] = isset($result['msg']) ? $result['msg'] : '';
        //是否为机构的新注册用户1:是；0:否；不为‘1’的情况当作‘0’处理, 2通过速贷之家推过来老用户，3其他渠道推过来的用户
        $datas['is_new_user'] = isset($result['is_new_user']) ? OauthStrategy::formatFaxindaiIsNewUser($result['is_new_user']) : 0;
        //对接平台返回对接信息记流水
        $log = OauthFactory::createDataProductAccessLog($datas);

        return $page ? $page : '';
    }

}
