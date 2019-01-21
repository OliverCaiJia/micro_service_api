<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-1-18
 * Time: 上午11:02
 */
namespace App\Services\Core\Data\Xiaoxiaojinrong\Config;

class XiaoxiaojinrongConfig {
    // 正式环境地址
    const URL = 'https://www.xxjr.com/cooper/org/thirdData/jdt';
    // 测试环境地址
    const UAT_URL = 'http://330874c5.nat123.cc/cooper/org/thirdData/jdt';
    // 商户号
    const CODE = 'sjdt170';

    /**
     * 获取请求地址
     * @return string
     */
    public static function getUrl()
    {
        return PRODUCTION_ENV ? static::URL : static::UAT_URL;
    }
}

