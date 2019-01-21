<?php

namespace App\Services\Core\Oneloan\Renxinyong\Config;

/**
 *  任信用配置
 */
class RenxinyongConfig
{
    //正式环境
    const FORMAL_URL = 'http://mld-app.boyacx.com:8422/bycx-rece-service/dock/user/isnew';
    //测试环境
    const TEST_URL = 'http://47.96.33.141:8730/bycx-rece-service/dock/user/isnew';
    //对应真实环境
    const REAL_URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    //数据来源
    const CHANNEL_CODE = '3635537';

}
