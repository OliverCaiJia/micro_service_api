<?php

namespace App\Services\Core\Oneloan\Youxin\Gongyinying\Config;


use App\Helpers\Utils;
use App\Strategies\SpreadStrategy;

class GongyinyingConfig
{
    //名单来源
    const SOURCE_ID = "dfs";
    //测试线地址
    const TEST_URL = 'https://test-hallelujah.ucredit.com/hallelujah/customerInfo/uploadSingleCustomerInfo';
    //正式线地址
    const FORMAL_URL = 'https://hallelujah.ucredit.com/hallelujah/customerInfo/uploadSingleCustomerInfo';

    //请求地址
    const URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    //用户名
    const USERNAME = 'sdfw';
    //密码
    const PASSWORD = 'errsfd56@';

    /**
     * 获取社保信息
     * 速贷之家：1:有，0:无,0
     * @param $IsSocial
     * @return mixed|string
     */
    public static function getSocialSecurity($isSocial)
    {
        $temp = [
            '1' => '有',
            '0' => '无',
        ];
        return isset($temp[$isSocial]) ? $temp[$isSocial] : '';
    }

    /**
     * 000 无公积金, 001 1年以内, 002 1年以上',
     * @param string $param
     * @return mixed|string
     */
    public static function getAccumulationFund($param = '')
    {
        $temp = [
            '000' => '无公积金',
            '001' => '1年以内',
            '002' => '1年以上',
        ];

        return isset($temp[$param]) ? $temp[$param] : '';
    }

    /**
     * 获取信用卡信息
     * 速贷之家：1:有，0:无,
     * @param $Creditcard
     * @return mixed|string
     */
    public static function getCreditcard($creditcard)
    {
        $temp = [
            '1' => '有',
            '0' => '无',
        ];
        return isset($temp[$creditcard]) ? $temp[$creditcard] : '';
    }

    /**
     * 获取信用卡信息
     * 速贷之家：1:有，0:无,
     * @param $Creditcard
     * @return mixed|string
     */
    public static function getSalaryExtend($salary)
    {
        $temp = [
            '001' => '银行转账',
            '002' => '现金发放',
        ];
        return isset($temp[$salary]) ? $temp[$salary] : '';
    }

    /**
     * 由用户名和密码生成,并放到请求header中，生成规则如下。（用户名和密码由友信分配）
     * @return string
     */
    public static function getAuthorization()
    {
        //用户名
        $username = GongyinyingConfig::USERNAME;
        //密码
        $pwd = GongyinyingConfig::PASSWORD;
        //base64加密
        $baseEncrypt = base64_encode($username . ':' . $pwd);
        //Authorization 
        $author = 'Basic' . ' ' . $baseEncrypt;

        return $author;
    }

    /**
     * 秒贷数据处理
     * @param array $params
     * @return array
     */
    public static function formatDatas($params = [])
    {
        $datas = [
            'city' => isset($params['city']) ? $params['city'] : '',//城市
            'name' => isset($params['name']) ? $params['name'] : '',//姓名
            'sex' => isset($params['sex']) ? $params['sex'] : 0,
            'certificate_no' => isset($params['certificate_no']) ? $params['certificate_no'] : '',//身份证号
            'mobile' => isset($params['mobile']) ? $params['mobile'] : '',//手机号
            'money' => isset($params['money']) ? $params['money'] : 0,//借款金额
            'salary' => isset($params['salary']) ? SpreadStrategy::formatSalaryAverage($params) : '', //月收入
            'social_security' => isset($params['social_security']) ? $params['social_security'] : 0,//是否有社保
            'accumulation_fund' => isset($params['accumulation_fund']) ? $params['accumulation_fund'] : '',//是否有公积金
            'has_creditcard' => isset($params['has_creditcard']) ? $params['has_creditcard'] : 0,//是否有信用卡
            'salary_extend' => isset($params['salary_extend']) ? $params['salary_extend'] : 0,//发薪方式
            'is_micro' => isset($params['is_micro']) ? $params['is_micro'] : 0,//微粒贷
        ];


        return $datas ? $datas : [];
    }
}