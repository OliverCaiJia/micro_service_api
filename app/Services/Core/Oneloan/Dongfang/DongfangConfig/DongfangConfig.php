<?php

namespace App\Services\Core\Oneloan\Dongfang\DongfangConfig;

/**
 *  东方金融配置
 */
class DongfangConfig
{
    //正式环境
    const FORMAL_URL = 'http://mirzr.rongzi.com/';
    //测试环境
    const TEST_URL = 'http://103.242.19.6:19999/';
    //对应真实环境
    const REAL_URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    //密码
    const SECRET_KEY = 'rongzi.com_8763';
    //来源
    const UTMSOURCE = '241';
    //跳转正式
    const DIR_FORMAL_URL = 'http://m.rongzi.com/';
    //跳转测试
    const DIR_TEST_URL = 'http://103.242.19.6:20000/';
    //跳转url
    const DIR_REAL_URL = PRODUCTION_ENV ? self::DIR_FORMAL_URL : self::DIR_TEST_URL;


    /**
     * 获取查询是否注册的token
     *
     * @param $params
     * @return string
     */
    public static function isRegisteredToken($params = [])
    {
        return md5($params['mobile'] . $params['time'] . DongfangConfig::SECRET_KEY);
    }

    /**
     * 获取注册token
     *
     * @param $params
     * @return string
     */
    public static function registeredToken($params = [])
    {
        return md5($params['cityname'] . $params['mobile'] . $params['name'] . $params['sex'] . $params['loanamount'] . DongfangConfig::UTMSOURCE . $params['time'] . DongfangConfig::SECRET_KEY);
    }

    /**
     * 获取getTonken的tokeh值
     *
     * @param array $params
     * @return string
     */
    public static function token($params = [])
    {
        return md5($params['mobile'] . DongfangConfig::UTMSOURCE . $params['time'] . DongfangConfig::SECRET_KEY);

    }

    /**
     * 获取跳转token
     *
     * @param array $params
     * @return string
     */
    public static function getDirToken($params = [])
    {
        return md5($params['token'] . DongfangConfig::UTMSOURCE . $params['time'] . DongfangConfig::SECRET_KEY);
    }

    /**
     * 参数整理
     *
     * @param array $params
     * @return array
     */
    public static function getParams($params = [])
    {
        $socialsecurityfund = self::fund($params);
        $identity = self::identity($params);
        $workingage = self::workingage($params);

        $arr = [
            'cityname' => $params['cityname'],
            'mobile' => $params['mobile'],
            'name' => $params['name'],
            'sex' => ($params['sex'] == 0) ? 2 : $params['sex'],
            'loanamount' => ceil($params['money'] / 10000),
            'age' => $params['age'],
            'havehouseloan' => ($params['house_info'] == '001') ? 1 : 0,
            'havecarloan' => ($params['car_info'] == '001') ? 1 : 0,
            'socialsecurityfund' => $socialsecurityfund,
            'havecreditcard' => $params['has_creditcard'],
            'identity' => $identity,
            'incomedistributiontype' => ($params['salary_extend'] == '001') ? 1 : 2,
            'workingage' => $workingage,
            'averagemonthlyincome' => ($params['salary'] == '004') ? 10000 : 4000,
            'workingcity' => $params['cityname'],
            'havehouse' => ($params['house_info'] == '000') ? 2 : 1,
            'havecar' => ($params['car_info'] == '000') ? 2 : 1,
        ];

        return $arr;
    }


    /**
     * 工作单位时间
     *
     * @param $params
     * @return int
     */
    public static function workingage($params = [])
    {
        switch ($params['work_hours'])
        {
            case '001':
                $workingage = 2;
                break;
            case '002':
                $workingage = 4;
                break;
            case '003':
                $workingage = 16;
                break;
            default:
                $workingage = 8;
        }

        return $workingage;
    }

    /**
     * 跟人身份
     *
     * @param array $params
     * @return int
     */
    public static function identity($params = [])
    {
        switch ($params['occupation'])
        {
            case '001':
                $identity = 4;
                break;
            case '003':
                $identity = 2;
                break;
            default:
                $identity = 8;
        }

        return $identity;
    }

    /**
     * 社保公积金情况
     *
     * @param array $params
     * @return int
     */
    public static function fund($params = [])
    {
        if($params['accumulation_fund'] != '000' && $params['social_security'] == 1) //有社保有公积金
        {
            $socialsecurityfund = 2;
        } elseif ($params['accumulation_fund'] == '000' && $params['social_security'] == 1) { //有社保无公积金
            $socialsecurityfund = 4;
        } elseif ($params['accumulation_fund'] != '000' && $params['social_security'] == 0) { //无社保有公积金
            $socialsecurityfund = 8;
        } else { //无社保无公积金
            $socialsecurityfund = 1;
        }

        return $socialsecurityfund;
    }
}
