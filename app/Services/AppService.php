<?php

namespace App\Services;

/**
 * 外部Http Service服务调用
 */
class AppService
{

    /**
     * Instantiate a new Controller instance.
     */
    public function __construct()
    {
        date_default_timezone_set('Asia/Shanghai'); //时区配置
    }

    private static $serve;

    public static function o($config = [])
    {
        if (!(self::$serve instanceof static)) {
            self::$serve = new static($config);
        }

        return self::$serve;
    }

    // 新版接口
    const API_URL = PRODUCTION_ENV ? 'https://api.jdt.com' : 'https://uat.api.jdt.com';
    // H5域名
    const M_URL = PRODUCTION_ENV ? 'http://m.jdt.com' : 'https://uat.m.jdt.com';
    // 活动域名
    const EVENT_URL = PRODUCTION_ENV ? 'http://event.jdt.com' : 'http://test.event.jdt.com';
    // Web网站
    const WEB_URL = PRODUCTION_ENV ? 'http://www.jdt.com' : 'http://test.www.jdt.com';
    // 旧版接口
    const MAPI_URL = PRODUCTION_ENV ? 'http://mapi.jdt.com' : 'http://test.mapi.jdt.com';

    // 七牛存储根目录
    const ENV_QINIU_PATH = PRODUCTION_ENV ? 'production/' : 'test/';

    // OpenSNS域名
    const SNS_URL = PRODUCTION_ENV ? 'https://sns.jdt.com/m/index.php' : 'https://uat.sns.jdt.com/m/index.php';
//    const SNS_URL = PRODUCTION_ENV ? '' : 'http://dd.opensns.com/m/index.php';

    //易宝回调
    //回调地址
    const YIBAO_CALLBACK_URL = PRODUCTION_ENV ? 'http://39.106.73.73' : 'https://uat.api.jdt.com';

    //同步
    const API_URL_YIBAO_SYN = '/v1/callback/payment/yibao/syncallbacks?type=';

    //异步
    const API_URL_YIBAO_ASYN = '/v1/callback/payment/yibao/asyncallbacks?type=';

    // 芝麻API
    const ZHIMA_API_URL = 'https://zmopenapi.zmxy.com.cn/openapi.do';
}
