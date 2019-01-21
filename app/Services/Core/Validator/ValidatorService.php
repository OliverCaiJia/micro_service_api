<?php

namespace App\Services\Core\Validator;

use App\Services\AppService;

class ValidatorService extends AppService
{

    const SCORPIO_API_URL = 'https://api.51datakey.com';
    const TIANCHUANG_API_URL = 'http://api.tcredit.com';
    const ZHIMA_API_URL = 'https://zmopenapi.zmxy.com.cn/openapi.do';
    //face++
    const FACEID_API_URL = 'https://api.faceid.com';

    public static $services;

    public static function i()
    {

        if (!(self::$services instanceof static)) {
            self::$services = new static();
        }

        return self::$services;
    }

    /**
     * 天创tokenid
     *
     * @return string
     */
    public static function getTianChuangTokenid()
    {
        return '2f57cee2-9317-4f95-9e72-c4551fbfa3c7';
    }

    /**
     * 天创appid
     *
     * @return string
     */
    public static function getTianChuangAppid()
    {
        return 'b0a98c44-607d-4beb-97dd-a62abd738ce6';
    }

    /**
     * 魔蝎apikey
     *
     * @param string $apikey
     * @return string
     */
    public static function getScorpioApiKey($apikey = 'apikey')
    {
        return $apikey . ' a7d197f11ff54578800a0dfe76a5e648';
    }

    /**
     * 魔蝎token
     *
     * @param string $token
     * @return string
     */
    public static function getScorpioToken($token = 'token')
    {
        return $token . ' 5d10c2bbf76c4sdfewrw976035353';
    }

    /**
     * 魔蝎回调接口中hmacsha256的生成秘钥
     *
     * @return string
     */
    public static function getScorpioCallBackSecret()
    {
        return '27c7e4bc51werdsvd9caf544771876';
    }

    /**
     * 芝麻信用评分appid
     * @return string
     */
    public static function getZhimaCreditScoreAppId()
    {
        return '134456430';
    }

    /**
     * 芝麻信用评分产品代码
     * @return string
     */
    public static function getScoreProductCode()
    {
        return 'w10101003240000001';
    }

    /**
     * 芝麻行业关注名单appid
     * @return string
     */
    public static function getZhimaCreditWatchlistAppId()
    {
        return '1004623486';
    }

    /**
     * 行业关注名单产品代码
     * @return string
     */
    public static function getWatchlistProductCode()
    {
        return 'w101010ewr01046457000000022';
    }

    /**
     * face++ AppKey
     * @return string
     */
    public static function getFaceidAppKey()
    {
        return PRODUCTION_ENV ? 'z81-ezAIUEUQcBI8RDtbcI3wrDL1ENqxtRf' : 'Dbu3X9xgX6FRT6Ft7ymdH8DGbuewtTRVlen';
    }

    /**
     * face++ AppSecret
     * @return string
     */
    public static function getFaceidAppSecret()
    {
        return PRODUCTION_ENV ? '001Y-YSN4DDGauyux8OrrrerwLEIixo_2am' : 'nyg1gwC2p96Zf0qwerhuK8QttzxEpuBgQD';
    }

}
