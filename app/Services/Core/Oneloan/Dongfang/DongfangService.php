<?php


namespace App\Services\Core\Oneloan\Dongfang;

use App\Helpers\Http\HttpClient;
use App\Services\AppService;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use App\Services\Core\Oneloan\Dongfang\DongfangConfig\DongfangConfig;

/**
 * 东方对接
 */
class DongfangService extends AppService
{

    /*
     * 检查是否注册
     *
     * @param array $params
     * @return mixed|string   false 未注册　　true 已注册
     */
    public static function isRegistered($params = [])
    {
        //链接地址
        $url = DongfangConfig::REAL_URL . 'rzr/Transfer/IsRegistered';
        $params['time'] = date('YmdHis');
        $sign = DongfangConfig::isRegisteredToken($params);
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

        if(isset($arr['Code']) && $arr['Code'] == 0)
        {
            return isset($arr['IsRegistered']) ? $arr['IsRegistered'] : "";
        }

        return "";
    }

    /*
     * 注册接口
     *
     * @param array $params
     * @return mixed
     */
    public static function register($params = [], callable $success, callable $fail)
    {   //请求url
        $url = DongfangConfig::REAL_URL . 'rzr/Transfer/Register';

        //来源标识
        $utmsource = DongfangConfig::UTMSOURCE;
        $params['time'] = date('YmdHis');
        $sign = DongfangConfig::registeredToken($params);
        //
        $request = [
            'json' => [
                'CityName' => $params['cityname'],                   //城市名字:拼音形式
                'CellPhoneNumber' => $params['mobile'],              //手机号
                'RealName' => $params['name'],                       //用户名
                'Gender' => $params['sex'],                          //性别
                'LoanAmount' => $params['loanamount'],               //贷款额度 单位:万
                'UtmSource' => $utmsource,
                'TimeStamp' => $params['time'],
                'Signature' => $sign,
//                'LoanPerod' => 6,//$params['loanperod'],                 //贷款期限 单位：月
                'Age' => $params['age'],                             //年龄
                'HaveHouseLoan' => $params['havehouseloan'],         //房贷情况 无：0 有：1
                'HaveCarLoan' => $params['havecarloan'],             //车贷情况 无：0 有：1
                'SocialSecurityFund' => $params['socialsecurityfund'], //社保公积金情况 无社保无公积金：1，有社保有公积金:2，有社保无公积金:4，无社保有公积金:8
                'HaveCreditCard' => $params['havecreditcard'],       //信用卡情况 无：0 有：1
                'Identity' => $params['identity'],                   //职业身份  企业主：1， 个体户、私营业主：2， 上班族： 4，其他：8
                'IncomeDistributionType' => $params['incomedistributiontype'], //收入发放类型   全部打卡：1 全部现金：2
                'WorkingAge' => $params['workingage'],               //现单位工作时间  6个月以下：2， 6-12个月：4， 12-24个月：8 ，24-36个月：16， 36个月以上：32
                'AverageMonthlyIncome' => $params['averagemonthlyincome'],  //月均总收入（元）  4000以下：4000,4000-5000：4500,5000-10000：7500,10000以上：10000
                'WorkingCity' => $params['workingcity'],             //工作所在地  （城市）拼音形式
//                'CreditCardAmount' => 32,//$params['creditcardamount'],   //信用卡额度  无信用卡:1, 1-500元 :2, 501-1000元:4 ,1001-5000元:8, 5001-8000元:16 ,8001-10000元:32 ,10001-50000元:64 ,50001-100000元:128 ,100001元及以上 :256
                'HaveHouse' => $params['havehouse'],                 //房产情况  有：1 无：2
                'HaveCar' => $params['havecar'],                     //车产情况  有：1 无：2
            ],
        ];

        $promise = HttpClient::i()->requestAsync('POST', $url, $request);

        $promise->then(
            function (ResponseInterface $res) use($success) {
                $result = $res->getBody()->getContents();
                $success(json_decode($result, true));
            },
            function (RequestException $e) use($fail) {
                $fail($e);
            }
        );

        $promise->wait();

    }

    /*
     * 获取登录凭证
     *
     * @param array $params
     * @return mixed
     */
    public static function getToken($params = [])
    {
        $url = DongfangConfig::REAL_URL . 'rzr/Transfer/GetToken';
        $params['time'] = date('YmdHis');
        $sign = DongfangConfig::token($params);
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
     * 跳转
     *
     * @param array $params
     * @return mixed
     */
    public static function redirectToUrl($params = [])
    {
        $url = DongfangConfig::DIR_REAL_URL . 'Transfer/RedirectToUrl';
        $params['time'] = date('YmdHis');
        $sign = DongfangConfig::getDirToken($params);
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
}

