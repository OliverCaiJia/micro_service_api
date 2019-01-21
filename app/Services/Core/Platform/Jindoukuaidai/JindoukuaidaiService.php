<?php

namespace App\Services\Core\Platform\Jindoukuaidai;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\Factory\OauthFactory;
use App\Services\Core\Platform\PlatformService;
use App\Services\Core\Platform\Jiufuwanka\Xianjin\Util\RsaUtil;
use App\Services\Core\Platform\Jindoukuaidai\Config\JindouConfig;

/**
 * 筋斗快贷 —— 筋斗快贷对接Service
 * Class JindoukuaidaiService
 * @package App\Services\Core\Platform\Jindoukuaidai
 */
class JindoukuaidaiService extends PlatformService
{
    /**
     * 筋斗快贷 对接地址
     * @param $mobile
     * @param $page
     * @return string
     */
    public static function fetchJindoukuaidaiUrl($datas)
    {
        // 用户&平台数据
        $mobile = $datas['user']['mobile'];       //手机号
        $name = empty($datas['user']['real_name']) ? '客户' : $datas['user']['real_name'];      // 真实姓名
        $page = $datas['page'];

        // 请求参数
        $medium = JindouConfig::MEDIUM;           // 渠道medium
        $ip = Utils::ipAddress();                 // 用户真实ip
        $timestamp = time() . '000';              // 时间戳 . 000
        $key = JindouConfig::KEY;                 // 加密字符串
        $uri = JindouConfig::URI;                 // 资源标识符

        // 生成签名
        $sign = self::getSign($name, $mobile, $medium, $ip, $uri, $key);

        // 请求参数
        $request = [
            'json' => [
                'name' => $name,
                'mobile' => $mobile,
                'medium' => $medium,
                'ip' => $ip,
                'timestamp' => $timestamp,
                'sign' => $sign,
            ],
        ];

        $url = JindouConfig::getUrl();

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $result = json_decode($result, true);

        $msg = '';
        if (isset($result['code'])) {
            // 成功
            if ($result['code'] == 0) {
                $url = $result['data'];
                //处理返回的值中的汉字格式
                $page = self::getResultUtfPage($url);
                $msg = '成功';
            } else {
                // 失败
                $msg = $result['msg'];
            }
        }

        // 生成流水
        self::insertLog($datas, $mobile, $url, $msg);

        return $page;
    }

    /** 获取sign
     * @param $mobile
     * @param $username
     * @return string
     */
    private static function getSign($name, $mobile, $medium, $ip, $url, $accessKey)
    {
        $signData = [
            'name' => $name,              // 用户姓名
            'mobile' => $mobile,            // 用户手机号
            'medium' => $medium,            // medium
            'ip' => $ip                 // 用户ip
        ];
        ksort($signData);
        $signText = '';
        foreach ($signData as $key => $val) {
            $signText = $signText . $key . '=' . $val;
            if (next($signData)) {
                $signText = $signText . "&";
            }
        }
        $signText = $url . '?' . $signText;

        // hmac_hash加密
        $sign = hash_hmac('sha1', $signText, $accessKey);
        return $sign;
    }

    /**
     * 生成对接产品申请流水
     * @param string $username
     * @param string $mobile
     * @param string $page
     * @param string $msg
     * @return bool
     */
    private static function insertLog($datas, $mobile = '', $page = '', $msg = '')
    {
        $datas['username'] = $datas['user']['username'];
        $datas['mobile'] = $mobile;
        $datas['channel_no'] = 'SDZJ';
        $datas['apply_url'] = $page;
        $datas['feedback_message'] = $msg;
        $datas['is_new_user'] = 0;
        $datas['complete_degree'] = '';
        $datas['qualify_status'] = 2;

        //对接平台记流水
        $log = OauthFactory::createDataProductAccessLog($datas);
        return $log;
    }

    /**
     * 处理url地址中的汉字
     * @param string $url
     * @return string
     */
    private static function getResultUtfPage($url = '')
    {
        $urls = explode('?', $url);
        //参数存在，将参数中的汉字进行utf8转义
        $query = [];
        if (isset($urls[1])) {
            $query = self::convertUrlQuery($urls[1]);
            foreach ($query as $key => $val) {
                //只对中文进行转义
                if (preg_match_all('/^[\x{4e00}-\x{9fa5}]+$/u', $val, $matches)) {
                    $query[$key] = urlencode($val);
                } else {
                    $query[$key] = $val;
                }
            }
        }
        //拼接url地址
        $resPage = self::getUrlQuery($query);

        return $urls[0] . '?' . $resPage;
    }


    /**
     * 参数存在，将参数中的汉字进行utf8转义
     * @param $query
     * @return array
     */
    private static function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    /**
     * 将参数变为字符串
     * @param $array_query
     * @return string
     */
    private static function getUrlQuery($array_query)
    {
        $tmp = array();
        foreach ($array_query as $k => $param) {
            $tmp[] = $k . '=' . $param;
        }
        $params = implode('&', $tmp);
        return $params;
    }

}