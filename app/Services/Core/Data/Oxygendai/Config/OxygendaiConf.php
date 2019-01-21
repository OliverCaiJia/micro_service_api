<?php

namespace App\Services\Core\Data\Oxygendai\Config;

class OxygendaiConf
{

    //获取测试线token链接
    const ACCESS_TOKEN_URL = PRODUCTION_ENV ? 'https://api.pingan.com.cn/oauth/oauth2/access_token' : 'https://test-api.pingan.com.cn:20443/oauth/oauth2/access_token';
    //测试线地址
    const URL = PRODUCTION_ENV ? 'https://api.pingan.com.cn/' : 'https://test-api.pingan.com.cn:20443/';
    //grant_type
    const  GRANT_TYPE = 'client_credentials';
    //客户端id
    const CLIENT_ID = PRODUCTION_ENV ? 'P_0125' : 'P_0129';
    //客户端密码
    const CLIENT_SECRET = PRODUCTION_ENV ? '5Ra37' : '49gH1';
    //获客渠道
    const RECEIVED_CHANNEL = 'WAP';
    //媒体来源  CXX-MKHUOKECEA-
    const MEDIA_SOURCE_CODE = 'CXX-MKHUOKECEA-jdt';
}
