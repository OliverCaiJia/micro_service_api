<?php

namespace App\Services\Core\Oneloan\Xiaoxiaojinrong;

use App\Helpers\DateUtils;
use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\AppService;
use App\Services\Core\Oneloan\Xiaoxiaojinrong\Config\XiaoxiaojinrongConfig;
use App\Strategies\SpreadStrategy;
use Illuminate\Support\Facades\Log;

/**
 * 小小金融 —— 接口对接Service
 * Class XiaoxiaojinrongService
 * @package App\Services\Core\Data\Xiaoxiaojinrong
 */
class XiaoxiaojinrongService extends AppService
{
    /**
     * 小小金融 —— 接口对接Service
     *
     * @param $datas
     * @return array
     */
    public static function spread($datas)
    {
        // 签名
        $sign = XiaoxiaojinrongConfig::getSign($datas['mobile'], XiaoxiaojinrongConfig::CODE);
        // 请求参数
        $request = [
            'form_params' => [
                'time' => XiaoxiaojinrongConfig::getMillionTime(),//date('YmdHis') . '000',
                'sign' => $sign,
                'telephone' => $datas['mobile'],
                'applyName' => $datas['name'],
                'birthday' => DateUtils::getBirthday($datas['birthday']), //1992-02-02
                'loanAmount' => round($datas['money'] / 10000, 2),
                'cityName' => $datas['city'], //上海市
                'sex' => $datas['sex'],
                'socialType' => $datas['social_security'] ? 1 : 2,
                'fundType' => $datas['accumulation_fund'] == '000' ? 2 : 1,
                'houseType' => $datas['house_info'] == '000' ? 2 : 1,
                'carType' => XiaoxiaojinrongConfig::getCarType($datas['car_info']),
                'wagesType' => XiaoxiaojinrongConfig::getSalaryExtend($datas['salary_extend']),
                'insurType' => $datas['has_insurance'] == 0 ? 0 : 1, //数字类型，（ 0无 1有
                'applyIp' => $datas['created_ip'],//Utils::ipAddress(),
                'haveWeiLi' => ($datas['is_micro'] == 1) ? 8000 : $datas['is_micro'],
            ],
        ];

        // 获取url
        $url = XiaoxiaojinrongConfig::getUrl();

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $resultObj = json_decode($result, true);

        return $resultObj;
    }
}