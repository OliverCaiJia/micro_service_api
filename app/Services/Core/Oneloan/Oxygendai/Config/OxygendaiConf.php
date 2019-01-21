<?php

namespace App\Services\Core\Oneloan\Oxygendai\Config;

class OxygendaiConf
{

    //获取测试线token链接
    const ACCESS_TOKEN_URL = PRODUCTION_ENV ? 'https://api.pingan.com.cn/oauth/oauth2/access_token' : 'https://test-api.pingan.com.cn:20443/oauth/oauth2/access_token';
    //测试线地址
    const URL = PRODUCTION_ENV ? 'https://api.pingan.com.cn/' : 'https://test-api.pingan.com.cn:20443/';
    //grant_type
    const  GRANT_TYPE = 'client_credentials';
    //客户端id
    const CLIENT_ID = PRODUCTION_ENV ? 'P_ZHIJIE20dsf180125' : 'P_ZHIJIE20sdf180129';
    //客户端密码
    const CLIENT_SECRET = PRODUCTION_ENV ? 'vMD5Ra37' : 'CQk49gH1';
    //获客渠道
    const RECEIVED_CHANNEL = 'WAP';
    //媒体来源
    const MEDIA_SOURCE_CODE_A = 'CXX-JDT-jdt';
    const MEDIA_SOURCE_CODE_B = 'CXX-JDT-jdt2';
    const MEDIA_SOURCE_CODE_C = 'CXX-JDT-jdt3';
    const MEDIA_SOURCE_CODE_AB = 'CXX-JDT-jdt4';
    const MEDIA_SOURCE_CODE_AC = 'CXX-JDT-jdt5';
    const MEDIA_SOURCE_CODE_BC = 'CXX-JDT-jdt6';
    const MEDIA_SOURCE_CODE_ABC = 'CXX-JDT-jdt7';

    /**
     * 整理数据
     *
     * @param $users
     * @return array
     */
    public static function getUserList($users)
    {
        $result = [];
        foreach ($users as $k => $user) {
            $result[$k]['name'] = $user['name'];
            $result[$k]['mobileNo'] = $user['mobile'];
            $result[$k]['ipAddr'] = $user['created_ip'];
            $result[$k]['mediaSourceCode'] = OxygendaiConf::getMediaSourceCode($user);
            $result[$k]['receivedChannel'] = OxygendaiConf::RECEIVED_CHANNEL;
        }

        return $result;
    }

    /**
     * 媒体来源代码
     * @param array $params
     * @return string
     */
    public static function getMediaSourceCode($params = [])
    {
        $code = '';
        //房
        $house = in_array($params['house_info'], ['001', '002']);
        //车
        $car = in_array($params['car_info'], ['002']);
        //寿险2400以上
        $hasInsurance = in_array($params['has_insurance'], ['2']);

        if ($house && !$car && !$hasInsurance)//房
        {
            $code = OxygendaiConf::MEDIA_SOURCE_CODE_A;
        }elseif (!$house && $car && !$hasInsurance)//车
        {
            $code = OxygendaiConf::MEDIA_SOURCE_CODE_B;
        }elseif (!$house && !$car && $hasInsurance)//寿险
        {
            $code = OxygendaiConf::MEDIA_SOURCE_CODE_C;
        }elseif ($house && $car && !$hasInsurance)//房+车
        {
            $code = OxygendaiConf::MEDIA_SOURCE_CODE_AB;
        }elseif ($house && !$car && $hasInsurance)//房+寿险
        {
            $code = OxygendaiConf::MEDIA_SOURCE_CODE_AC;
        }elseif (!$house && $car && $hasInsurance)//车+寿险
        {
            $code = OxygendaiConf::MEDIA_SOURCE_CODE_BC;
        }elseif ($house && $car && $hasInsurance)//房+车+寿险
        {
            $code = OxygendaiConf::MEDIA_SOURCE_CODE_ABC;
        }

        return $code;
    }

}
