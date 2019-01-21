<?php

namespace App\Services\Core\Oneloan\Hengchang\Hengyidai;

use App\Helpers\Http\SoapClient;
use App\Services\AppService;
use App\Services\Core\Oneloan\Hengchang\Hengyidai\HengyidaiConfig\HengyidaiConfig;
use App\Strategies\SpreadStrategy;

/**
 * 恒昌线下恒易贷对接
 */
class HengyidaiService extends AppService
{
    /**
     * 注册接口
     *
     * @param array $params
     * @return mixed
     */
    public static function register($params = [])
    {
        //请求url
        $url = HengyidaiConfig::URL . '/Api/WebService/PushData.php?wsdl';

        //cred:一个 json 字符串， 用于传入用户名密码
        $credential = [
            'UserName' => HengyidaiConfig::USERNAME,
            'Password' => HengyidaiConfig::PASSWORD,
        ];

        //加密
        $encryptCred = HengyidaiConfig::encrypt(json_encode($credential, JSON_UNESCAPED_UNICODE));

        //request: 一个 json 字符串，用于传入待推送的用户数据
        $req = [
            'Id' => $params['id'],
            'Name' => $params['name'],
            'TelNo' => $params['mobile'],
            'Age' => $params['age'],  //选填
            'IdNumber' => isset($params['certificate_no']) ? $params['certificate_no'] : '-',
            'Salary' => SpreadStrategy::formatSalaryAverage($params), //选填 月均收入，单位：元
            'Loan' => intval($params['money']), //选填 贷款额度，单位：元
//            'Credit' => $params['credit'], //选填 征信记录
            'City' => $params['city'], //必填 城市(需精确到区县，格式如下：xx省|xx市|xx区或xx县)
            'CityCode' => $params['cityCode'], //必填,六位数字
            'Career' => HengyidaiConfig::formatOccupation($params), //选填 职业
//            'CompanyType' => $params['companyType'], //选填 企业类型
//            'CreditCondition' => $params['creditCondition'], //选填 信用情况
//            'ServiceAge' => HengyidaiConfig::formatWorkHours($params), //选填 当前单位工龄，单位：年
//            'LoanPeriod' => $params['loanPeriod'], //选填 借款期限（月）
            'House' => HengyidaiConfig::formatHouseInfo($params), //选填 是否名下有房产
            'Car' => $params['car_info'] == '000' ? '否' : '是', //选填 是否名下有车
            'Gongjijin' => $params['accumulation_fund'] == '000' ? '否' : '是', //选填 是否有本地公积金
            'SocialSecurity' => $params['social_security'] == 0 ? '否' : '是', //选填 是否有社保
            'Policy' => $params['has_insurance'] == 0 ? '否' : '是', //选填 是否有保单
            'CreditCard' => $params['has_creditcard'] == 0 ? '否' : '是', //选填 是否有信用卡
        ];

        //加密
        $encryptReq = HengyidaiConfig::encrypt(json_encode($req, JSON_UNESCAPED_UNICODE));
        //code
        $code = HengyidaiConfig::HENGYIDAI_CODE;

        $ret = SoapClient::i($url)->pushUserData($code, $encryptReq, $encryptCred);

        $res = json_decode($ret, true);

        return $res;
    }
}

