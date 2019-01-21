<?php

namespace App\Services\Core\Oneloan\Heiniu\Config;

use App\Services\Core\Oneloan\Heiniu\Util\DesUtil;

class HeiniuConfig
{
    // url
    const URL = PRODUCTION_ENV ? 'http://www.heiniubao.com/insurance/enhanced' : 'http://47.92.10.7:9099/insurance/enhanced';
    // channel
    const CHANNEL = 'jdt';
    // subchannel
    const SUBCHANNEL = 'jdtapi1';
    // des key
    const DES_KEY = 'a1d80c';

    /**
     * 对身份证号进行处理
     *
     * @param array $data
     * @return array
     */
    public static function getParams($data = [])
    {
        $arr = [];
        $encryptedSex = DesUtil::i()->encrypt($data['sex'], HeiniuConfig::DES_KEY);
        $encryptedBirth = DesUtil::i()->encrypt($data['birthday'], $key = HeiniuConfig::DES_KEY);
        if (isset($data['certificate_no']) && !empty($data['certificate_no'])) {
            $sign = self::getIdNoSign($data['certificate_no'], $data['name'], $data['mobile'], HeiniuConfig::CHANNEL);
            $encryptedIdNo = DesUtil::i()->encrypt($data['certificate_no'], $key = HeiniuConfig::DES_KEY);
            $arr = [
                'id_no' => $encryptedIdNo,
                'sign' => $sign,
            ];
        } else {
            $sign = self::getSign($data['name'], $data['mobile'], HeiniuConfig::CHANNEL);
            $arr = [
                'sex' => $encryptedSex,
                'birth' => $encryptedBirth,
                'sign' => $sign,
            ];
        }

        return $arr;
    }

    /**
     * 获取签名
     * @param string $name
     * @param string $phone
     * @param string $channel
     * @return string
     */
    public static function getSign($name = '', $phone = '', $channel = '')
    {
        return md5($name . $phone . $channel . 'baoxian-$@');
    }

    /**
     * 身份证签名
     *
     * @param $idNo
     * @param $name
     * @param $phone
     * @param $channel
     * @return string
     */
    public static function getIdNoSign($idNo, $name, $phone, $channel)
    {
        return md5($idNo . $name . $phone . $channel . 'baoxian-$@');
    }

    /**
     * 黑牛增加信息
     * loan_amount 贷款金额    43020 具体金额
     * credit_card  是否有信用卡  0 无  1 有
     * house  是否有房  0 名下无房  1 有房无贷 2 有房贷
     * profession  职业  0:上班族 1:私企业主 2:公务员
     * income  收入  收入范围 （如: 5000-1万）
     * working_time  工作时间  6:6个月   12:12个月   24:24个月
     * social_insurance  有无社会保险  0 无  1 有
     *
     * 示例:
     * {"loan_amount": "43020", "creditcard": "0", "house": "1", "profession": "0", "income": "5000-1万", "working_time": "12",
     * "social_insurance": "1"}
     *
     * 通过接口的custom字段传递过来，注意custom字段的内容要先转成json格式，然后加密传过来
     * @param array $datas
     * @return string
     */
    public static function createCustomer($datas = [])
    {
        $houseInfo = isset($datas['house_info']) ? HeiniuConfig::formatHouse($datas['house_info']) : '';
        $occupation = isset($datas['occupation']) ? HeiniuConfig::formatOccupation() : '';
        $customer = [
            'loan_amount' => isset($datas['money']) ? $datas['money'] . '' : '',
            'credit_card' => isset($datas['has_creditcard']) ? $datas['has_creditcard'] . '' : '',
            'house' => $houseInfo . '',
            'profession' => $occupation . '',
            'income' => isset($datas['salary']) ? HeiniuConfig::formatSalary($datas['salary']) : '',
            'working_time' => isset($datas['work_hours']) ? HeiniuConfig::formatWorkHours($datas['work_hours']) : '',
            'social_insurance' => isset($datas['social_security']) ? $datas['social_security'] . '' : '',
        ];

        return json_encode($customer);
    }

    /**
     * 房产信息, 000无房, 001有房贷, 002无房贷
     * @param string $param
     * @return int
     */
    public static function formatHouse($param = '')
    {
        if ('000' == $param) return 0;
        elseif ('001' == $param) return 2;
        elseif ('002' == $param) return 1;
        else return 0;
    }

    /**
     * 职业  0:上班族 1:私企业主 2:公务员
     * @param string $param
     * @return int
     */
    public static function formatOccupation($param = '')
    {
        if ('001' == $param) return 0;
        elseif ('002' == $param) return 2;
        elseif ('003' == $param) return 1;
        else return 0;
    }

    /**
     * income  收入  收入范围 （如: 5000-1万）
     *  001:2000以下，002:2000-5000,003:5000-1万，004：1万以上',
     * @param string $param
     * @return int
     */
    public static function formatSalary($salary = '')
    {
        $tmp = [
            '001' => '0~2000',
            '002' => '2000~5000',
            '003' => '5000~1万',
            '004' => '1万以上',
            '101' => '0~2000',
            '102' => '2000~3000',
            '103' => '3000~4000',
            '104' => '4000~5000',
            '105' => '5000~1万',
            '106' => '1万以上',
        ];

        if (isset($tmp[$salary])) {
            return $tmp[$salary];
        }

        return '';
    }

    /**
     * 工作时间  6:6个月   12:12个月   24:24个月
     *  001 6个月内, 002 12个月内, 003 1年以上',
     * @param string $param
     * @return int|string
     */
    public static function formatWorkHours($param = '')
    {
        if ('001' == $param) return '6';
        elseif ('002' == $param) return '12';
        elseif ('003' == $param) return '24';
        else return '';
    }
}