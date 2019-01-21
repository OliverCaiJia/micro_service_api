<?php

namespace App\Services\Core\Platform\Jindoukuaidai\Config;

class JindouConfig {

    // 域名地址
    const DOMAIN = 'https://jin-api-t.51huaxin.cn';
    const URI = '/user/login';
    // medium
    const MEDIUM = '3210';
    // 加密私钥
    const KEY = '11d814daa734519223b34347e798a8';

    public static function getUrl()
    {
        return static::DOMAIN . static::URI;
    }

}