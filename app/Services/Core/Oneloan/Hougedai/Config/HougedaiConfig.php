<?php

namespace App\Services\Core\Oneloan\Hougedai\Config;

/**
 *  猴哥贷配置
 */
class HougedaiConfig
{
    //正式环境
    const FORMAL_URL = 'http://www.jdt.com/apiforpushdata/apifordx/dataforapi.html';
    //测试环境
    const TEST_URL = 'http://www.jdt.com/apiforpushdata/apifordx/apifortest.html';
    //对应真实环境
    const REAL_URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;

    //渠道CODE
    const CODE = 'A2055593362245C7915404A757FB107B';

    /**
     * 类型转换
     * @param $type
     * @return int
     */
    public static function formatType($type = '')
    {
        switch ($type) {
            case '001':
                $typeInt = 1;
                break;
            case '002':
                $typeInt = 2;
                break;
            case '003':
                $typeInt = 3;
                break;
            default:
                $typeInt = 0;
        }
        return $typeInt;
    }

}
