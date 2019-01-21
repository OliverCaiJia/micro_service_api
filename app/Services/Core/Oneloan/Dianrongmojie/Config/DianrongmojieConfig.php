<?php

namespace App\Services\Core\Oneloan\Dianrongmojie\Config;

use App\Helpers\DateUtils;
use App\Services\Core\Oneloan\Paipaidai\Config\PaipaidaiConfig;
use App\Strategies\SpreadStrategy;

class DianrongmojieConfig
{
    // 正式环境地址
    const URL = 'https://cps.ppdai.com/bd/RegPostLisgting';

    // CHANNEL
    const CHANNEL = '38';

    // SOURCE
    const SOURCE = '46';

    // TOKEN
    const TOKEN = '67492f946c7efd947ed55bc';

    /**
     * 获取请求地址
     * @return string
     */
    public static function getUrl()
    {
        return static::URL;
    }

    /**
     * 获取签名
     * @param string $token
     * @param string $phone
     * @return string
     */
    public static function getSign($token = '', $phone = '')
    {
        $param_md5_str = md5("token=$token&phone=$phone");
        return md5("token=$token&phone=$phone&paramMd5=$param_md5_str");
    }

    /**
     * 获取月收入范围
     * 月收入范围, 001:2000以下，002:2000-5000,003:5000-1万，004：1万以上
     * @param $salary
     * @return mixed|string
     */
    public static function getSalary($salary)
    {
        $tmp = [
            '001' => '0~2000',
            '002' => '2000~5000',
            '003' => '5000~10000',
            '004' => '1w',
            '101' => '0~2000',
            '102' => '2000~3000',
            '103' => '3000~4000',
            '104' => '4000~5000',
            '105' => '5000~10000',
            '106' => '1w',
        ];

        if (isset($tmp[$salary])) {
            return $tmp[$salary];
        }

        return '';
    }

    /**
     * 获取职业
     */
    public static function getOccupation($occupation = '')
    {
        $occupations = [
            '001' => '上班族',
            '002' => '公务员',
            '003' => '私营业主',
        ];

        return isset($occupations[$occupation]) ? $occupations[$occupation] : '';
    }

    /**
     * 出生年  1992-05-16 00:00:00 => 1992
     * @param string $birthday
     * @return string
     */
    public static function getBirthdayYear($birthday = '')
    {
        if (empty($birthday)) return '';
        $birthday = DateUtils::getBirthday($birthday);

        $birthdays = explode('-', $birthday);

        return $birthdays ? $birthdays[0] : '';
    }

    /**
     * 获取信息
     *
     * @param array $datas
     * @return string
     */
    public static function getInfo($datas)
    {
        return json_encode([
            'applyCity' => $datas['city'],                              // 城市
            'captcha' => '202659',                                    //验证码
            'chechan' => $datas['car_info'] == '000' ? '无' : '有',   // 车产有无
            'chushengnian' => PaipaidaiConfig::getBirthdayYear($datas['birthday']), // 出生年
            'daikuanlx' => '个人贷款',                                   // 约定默认 "个人贷款"
            'daikuanyt' => '日常消费',                                   // 约定默认 "日常消费"
            'fangchan' => $datas['house_info'] == '000' ? '无' : '有',  // 房产有无
            'from' => 'sudaizhijia',                               // from
            'gongzixingshi' => '现金',                                   // 工资形式
            'money' => $datas['money'],                             // 贷款金额
            'month' => 7,                                           // 约定默认 7
            'name' => $datas['name'],                              // 真实姓名
            'phone' => $datas['mobile'],                            // 手机号
            'shebao' => $datas['social_security'] == 1 ? '有' : '无', // 社保有无
            //'shenfenzh' => $datas['idcard'],                             //身份证号
            'shenfenzh' => $datas['certificate_no'],                             //身份证号
            'xinyong' => $datas['has_creditcard'] == 1 ? '有' : '无',  // 信用卡有无
            'yuexin' => SpreadStrategy::getSalary($datas['salary']),              // 薪水
            'zhiye' => PaipaidaiConfig::getOccupation($datas['occupation'])     // 职业
        ]);
    }
}

