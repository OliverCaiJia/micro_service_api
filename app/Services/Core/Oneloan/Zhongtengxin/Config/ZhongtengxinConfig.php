<?php

namespace App\Services\Core\Oneloan\Zhongtengxin\Config;

class ZhongtengxinConfig
{
    //测试环境channel
    const TEST_CHANNEL = 'ewr';
    //测试环境
    const TEST_URL = 'http://jkbeta.chinatopcredit.com/loanInfo/applyLoan?callback=?&';

    //正式环境channel  WAPZJ1,WAPZJ2都可以标识渠道
    const FORMAL_CHANNEL = 'sdf1';
    //正式环境   https://awaken.chinatopcredit.com/?
    const FORMAL_URL = 'http://jk.chinatopcredit.com/loanInfo/applyLoan?callback=?&';

    //地址
    const URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    //渠道
    const CHANNEL = PRODUCTION_ENV ? self::FORMAL_CHANNEL : self::TEST_CHANNEL;

    /**
     * 获取月收入范围(跟产品部确认最终的)
     * 月收入范围, 0-1999/2000-2999/3000-3999/4000-5999/
     * 6000-9999/10000以上
     * 速贷之家：001:2000以下，002:2000-5000,003:5000-1万，004：1万以上',
     * 月收入范围,;101:2千以下，102:2千-3千，103:3千-4千，104:4千-5千，105:5千-1万，106:1万以上
     * @param $salary
     * @return mixed|string
     */
    public static function getSalary($salary)
    {
        $tmp = [
            '001' => '0-1999',
            '002' => '3000-3999',
            '003' => '6000-9999',
            '004' => '10000以上',
            '101' => '0-1999',
            '102' => '2000-2999',
            '103' => '3000-3999',
            '104' => '4000-5999',
            '105' => '6000-9999',
            '106' => '10000以上',
        ];

        if (isset($tmp[$salary])) {
            return $tmp[$salary];
        }

        return '';
    }

    /**
     * 获取职业(跟产品部确认最终的)
     * 001上班族, 002公务员, 003私营业主',
     * 工薪族／公务员／个体户／小企业主
     * @param string $occupation
     * @return mixed|string
     */
    public static function getOccupation($occupation = '')
    {
        $occupations = [
            '001' => '工薪族',
            '002' => '公务员',
            '003' => '小企业主',
        ];
        return isset($occupations[$occupation]) ? $occupations[$occupation] : '';
    }

}