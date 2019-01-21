<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-1-26
 * Time: 下午1:55
 */
namespace App\Services\Core\Data\Dongfang\Config;

/**
 *
 */
class DongfangConfig
{
    //正式环境
    const FORMAL_URL = 'http://mirzr.rongzi.com/';
    //测试环境
    const TEST_URL = 'http://103.242.169.6:1999/';
    //对应真实环境
    const REAL_URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    //密码
    const SECRET_KEY = 'rongzi.com_863';
    //来源
    const UTMSOURCE = '21';
    //跳转正式
    const DIR_FORMAL_URL = 'http://m.rongzi.com/';
    //跳转测试
    const DIR_TEST_URL = 'http://103.242.169.6:2000/';
    //跳转url
    const DIR_REAL_URL = PRODUCTION_ENV ? self::DIR_FORMAL_URL : self::DIR_TEST_URL;
}
