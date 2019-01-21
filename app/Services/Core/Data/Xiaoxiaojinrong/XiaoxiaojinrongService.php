<?php

namespace App\Services\Core\Data\Xiaoxiaojinrong;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\Core\Data\Xiaoxiaojinrong\Config\XiaoxiaojinrongConfig;
use App\Services\AppService;
use App\Strategies\SpreadStrategy;
use App\Models\Factory\UserSpreadFactory;
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
     * @param $datas
     */
    public static function spread($datas)
    {   // 签名
        $sign = self::getSign($datas['mobile'], XiaoxiaojinrongConfig::CODE);
        // 请求参数
        $request = [
            'form_params' => [
                'time' => self::getMillionTime(),//date('YmdHis') . '000',
                'sign' => $sign,
                'telephone' => $datas['mobile'],
                'applyName' => $datas['name'],
                'birthday' => $datas['birthday'],
                'loanAmount' => $datas['money'],
                'cityName' => $datas['city'],
                'sex' => $datas['sex'],
                'socialType' => $datas['social_security'],
                'fundType' => $datas['accumulation_fund'],
                'houseType' => $datas['house_info'],
                'workType' => $datas['occupation'],
                'carType' => $datas['car_info'],
                'wagesType' => $datas['salary_extend'],
                'insurType' => $datas['has_insurance'],
                'applyIp' => $datas['ip']
            ]
        ];
        
        // 获取url
        $url = XiaoxiaojinrongConfig::getUrl();
        
        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $resultObj = json_decode($result, true);
        
        return $resultObj;
    }
    
    public static function getMessage($code = '')
    {
        $msgArr = [
            '000' => '接收成功',
            '001' => '缺少必要参数',
            '002' => '签名有误',
            '003' => '申请重复',
            '004' => '接收异常',
            '008' => '未找到对应的城市',
        ];
        
        return isset($msgArr[$code]) ? $msgArr[$code] : '无错误信息';
    }
    
    /**
     * 获取签名
     * @param string $mobile
     * @param $code
     * @return string
     */
    public static function getSign($mobile = '', $code)
    {
        $milliTime = self::getMillionTime();
        
        return md5($mobile . '&' . $milliTime . $code);
    }
    
    /**
     * 获取毫秒时间戳
     *
     * @return string
     */
    public static function getMillionTime()
    {
        //获取毫秒时间
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec * 1000);
        $millisecond = str_pad($msec, 3, '0', STR_PAD_RIGHT);
        $milliTime = date("YmdHis") . $millisecond;
        
        return $milliTime;
    }
}