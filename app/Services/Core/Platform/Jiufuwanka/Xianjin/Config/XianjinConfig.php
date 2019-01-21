<?php

namespace App\Services\Core\Platform\Jiufuwanka\Xianjin\Config;

class XianjinConfig {
    // 域名
    const DOMAIN = PRODUCTION_ENV ? 'https://wkport.9fbank.com' : 'https://wkporttest.9fbank.com';
    // URI
    const URI = '/amity/confParter/wkUnionLogin';// 联合登录地址
    const SELECT_URI = '/amity/confParter/getUnionUserInfo'; // 查询接口
    // 测试线参数
    const UAT_PARTNER_ID = '523451542'; // 玖富万卡为友商提供的id

    // 正式线
    const PARTNER_ID = '5235754405843';     // 玖富万卡为友商提供的id

    /**
     * 查询接口
     * @return string
     */
    public static function getSelectUrl()
    {
        return PRODUCTION_ENV ? static::DOMAIN . static::SELECT_URI : static::DOMAIN . static::SELECT_URI;
    }

    /**
     * 联合登录url
     * @return string
     */
    public static function getLoginUrl()
    {
        return PRODUCTION_ENV ? static::DOMAIN . static::URI  : static::DOMAIN . static::URI;
    }


    /**
     * 合作商id
     * @return string
     */
    public static function getPartnerId()
    {
        return PRODUCTION_ENV ? static::PARTNER_ID : static::UAT_PARTNER_ID ;
    }

}