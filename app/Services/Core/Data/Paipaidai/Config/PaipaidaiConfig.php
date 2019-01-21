<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-1-18
 * Time: 上午11:02
 */
namespace App\Services\Core\Data\Paipaidai\Config;

class PaipaidaiConfig {
    // 正式环境地址
    const URL = 'https://cps.ppdai.com/bd/RegPostLisgting';

    // CHANNEL
    const CHANNEL = '30';

    // SOURCE
    const SOURCE = '47';

    // TOKEN
    const TOKEN = '67492f942b7efd947ed55bc';

    /**
     * 获取请求地址
     * @return string
     */
    public static function getUrl()
    {
        return static::URL;
    }
}

