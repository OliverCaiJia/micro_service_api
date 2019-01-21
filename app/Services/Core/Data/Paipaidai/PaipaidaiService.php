<?php

namespace App\Services\Core\Data\Paipaidai;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\Core\Data\Paipaidai\Config\PaipaidaiConfig;
use App\Services\AppService;
use App\Strategies\SpreadStrategy;
use App\Models\Factory\UserSpreadFactory;
use Illuminate\Support\Facades\Log;

/**
 * 拍拍贷 —— 接口对接Service
 * Class PaipaidaiService
 * @package App\Services\Core\Data\Paipaidai
 */
class PaipaidaiService extends AppService
{
    /**
     * 拍拍贷 —— 接口对接Service
     * @param $datas
     */
    public static function spread($datas)
    {
        $token = PaipaidaiConfig::TOKEN;
        // 请求参数
        $request = [
            'form_params' => [
                'ChannelId' => PaipaidaiConfig::CHANNEL,               // CHANNEL
                'SourceId'  => PaipaidaiConfig::SOURCE,                // SOURCE
                'token'     => $token,                                 // TOKEN
                'sign'      => self::getSign($token, $datas['mobile']),// 签名
                'phone'       => $datas['mobile'],                     // 手机号
                'userName'    => $datas['name'],                   // 姓名
                'shenfenzh'   => $datas['certificate_no'],             // 身份证号
                'applyLoanAmount' => $datas['money'],                  //贷款金额
                'applyLoanMonth'  => 7,                                // 贷款期限, 约定默认7
                'daikuanyt'       => '日常消费',                        // 贷款用途
                'loanType'        => '个人贷款',                        // 贷款类型, 非必填约定默认"个人贷款"
                'info'            => self::getInfo($datas)
            ]
        ];
        // 获取url
        $url = PaipaidaiConfig::URL;

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $resultObj = json_decode($result, true);

        return $resultObj;
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
     * 获取信息
     * @param array $datas
     * @return string
     */
    public static function getInfo($datas) {
        return json_encode([
            'applyCity' => $datas['city'],                              // 城市
            'captcha'   => '202659',                                    //验证码
            'chechan'   => $datas['car_info'] == '000' ? '无' : '有',   // 车产有无
            'chushengnian' => $datas['chushengnian'],                   // 出生年
            'daikuanlx' => '个人贷款',                                   // 约定默认 "个人贷款"
            'daikuanyt' => '日常消费',                                   // 约定默认 "日常消费"
            'fangchan'  => $datas['house_info'] == '000' ? '无' : '有',  // 房产有无
            'from'      => 'sudaizhijia',                               // from
            'gongzixingshi' => '现金',                                   // 工资形式
            'money'     => $datas['money'],                             // 贷款金额
            'month'     => 7,                                           // 约定默认 7
            'name'      => $datas['name'],                              // 真实姓名
            'phone'     => $datas['mobile'],                            // 手机号
            'shebao'    => $datas['social_security'] == 1 ? '有' : '无', // 社保有无
            'shenfenzh' => $datas['idcard'],                             //身份证号
            'xinyong'   => $datas['has_creditcard'] == 1 ? '有' : '无',  // 信用卡有无
            'yuexin'    => self::getSign($datas['salary']),              // 薪水
            'zhiye'     => self::getOccupation($datas['occupation'])     // 职业
        ]);
    }

    /**
     * 获取月收入范围
     * @param $salary
     * @return mixed|string
     */
    public static function getSalary($salary) {
        $tmp = [
            '001' => '0~3000',
            '002' => '3000~10000',
            '003' => '10000~100000'
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
            '003' => '私营业主'
        ];

        return isset($occupations[$occupation]) ? $occupations[$occupation] : '';
    }
}