<?php

namespace App\Services\Core\Oneloan\Rongshidai\RongshidaiConfig;

/**
 *  融时代配置
 */
class RongshidaiConfig
{
    //正式环境
    const FORMAL_URL = 'https://ccs.rstyle.com/receiveCustInfo';
    //测试环境
    const TEST_URL = 'http://test01-ccs.rongera.com/ccseb/receiveCustInfo';
    //对应真实环境
    const REAL_URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    //数据来源
    const SYS_TYPE = 'FFS01';
   
}
