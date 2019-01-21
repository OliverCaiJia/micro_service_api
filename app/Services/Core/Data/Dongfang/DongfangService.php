<?php

namespace App\Services\Core\Data\Dongfang;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use App\Services\Core\Data\Dongfang\Config\DongfangConfig;

/**
 * 东方对接
 */
class DongfangService extends AppService
{

    /**
     * 检查是否注册
     * @param array $params
     * $param
     */
    public static function isRegistered($params = [])
    {
        //链接地址
        $url = DongfangConfig::REAL_URL . 'rzr/Transfer/IsRegistered';
        $params['time'] = date('YmdHis');
        $sign = self::isRegisteredToken($params);
        //整理参数
        $request = [
            'json' => [
                'CellPhoneNumber' => $params['mobile'],
                'TimeStamp' => $params['time'],
                'Signature' => $sign,
            ],
        ];
        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $arr = json_decode($result, true);

        return $arr;

    }

    /**注册接口
     * @param array $params
     */
    public static function register($params = [])
    {   //请求url
        $url = DongfangConfig::REAL_URL . 'rzr/Transfer/Register';

        //来源标识
        $utmsource = DongfangConfig::UTMSOURCE;
        $params['time'] = date('YmdHis');
        $sign = self::registeredToken($params);
        //
        $request = [
            'json' => [
                'CityName' => $params['cityname'],
                'CellPhoneNumber' => $params['mobile'],
                'RealName' => $params['realname'],
                'Gender' => $params['gender'],
                'LoanAmount' => $params['loanamount'],
                'UtmSource' => $utmsource,
                'TimeStamp' => $params['time'],
                'Signature' => $sign,
                'LoanPerod' => $params['loanperod'],
                'Age' => $params['age'],
                'HaveHouseLoan' => $params['havehouseloan'],
                'HaveCarLoan' => $params['havecarloan'],
                'SocialSecurityFund' => $params['socialsecurityfund'],
                'HaveCreditCard' => $params['havecreditcard'],
                'Identity' => $params['identity'],
                'IncomeDistributionType' => $params['incomedistributiontype'],
                'WorkingAge' => $params['workingage'],
                'AverageMonthlyIncome' => $params['averagemonthlyincome'],
                'WorkingCity' => $params['workingcity'],
                'CreditCardAmount' => $params['creditcardamount'],
                'HaveHouse' => $params['havehouse'],
                'HaveCar' => $params['havecar'],
            ],
        ];

        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $arr = json_decode($result, true);

        return $arr;

    }

    /**
     *获取登录凭证
     */
    public static function getToken($params = [])
    {
        $url = DongfangConfig::REAL_URL . 'rzr/Transfer/GetToken';
        $params['time'] = date('YmdHis');
        $sign = self::token($params);
        //整理参数
        $request = [
            'json' => [
                'CellPhoneNumber' => $params['mobile'],
                'UtmSource' => DongfangConfig::UTMSOURCE,
                'TimeStamp' => $params['time'],
                'Signature' => $sign,
            ],
        ];
        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $arr = json_decode($result, true);

        return $arr;

    }

    /**
     *跳转
     */
    public static function redirectToUrl($params = [])
    {
        $url = DongfangConfig::DIR_REAL_URL . 'Transfer/RedirectToUrl';
        $params['time'] = date('YmdHis');
        $sign = self::getDirToken($params);
        //整理参数
        $request = [
            'json' => [
                'Token' => $params['token'],
                'UtmSource' => DongfangConfig::UTMSOURCE,
                'TimeStamp' => $params['time'],
                'Signature' => $sign,
            ],
        ];
        $response = HttpClient::i(['verify' => false])->request('GET', $url, $request);
        $result = $response->getBody()->getContents();
        $arr = json_decode($result, true);

        return $arr;

    }

    /**获取查询是否注册的token
     * @param $params
     * @return string
     */
    public static function isRegisteredToken($params = [])
    {
        return md5($params['mobile'] . $params['time'] . DongfangConfig::SECRET_KEY);
    }

    /**获取注册token
     * @param $params
     * @return string
     */
    public static function registeredToken($params = [])
    {
        return md5($params['cityname'] . $params['mobile'] . $params['realname'] . $params['gender'] . $params['loanamount'] . DongfangConfig::UTMSOURCE . $params['time'] . DongfangConfig::SECRET_KEY);
    }

    /**
     * 获取getTonken的tokeh值
     */
    public static function token($params = [])
    {
        return md5($params['mobile'] . DongfangConfig::UTMSOURCE . $params['time'] . DongfangConfig::SECRET_KEY);

    }

    /**
     * 获取跳转token
     */
    public static function getDirToken($params = [])
    {
        return md5($params['token'] . DongfangConfig::UTMSOURCE . $params['time'] . DongfangConfig::SECRET_KEY);
    }
}

