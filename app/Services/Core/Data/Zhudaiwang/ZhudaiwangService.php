<?php

namespace App\Services\Core\Data\Zhudaiwang;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\Core\Data\Zhudaiwang\Config\ZhudaiwangConfig;
use App\Services\AppService;
use App\Models\Factory\UserSpreadFactory;
use Illuminate\Support\Facades\Log;

/**
 * 助贷网 —— 助贷网贷款接口对接Service
 * Class ZhudaiwangService
 * @package App\Services\Core\Platform\Zhudaiwang
 */
class ZhudaiwangService extends AppService
{
    /**
     * 助贷网 —— 助贷网贷款接口对接Service
     * @param $datas
     * @return array
     */
    public static function spread($datas)
    {
        $request = [
            'form_params' => [
                'name'    => $datas['name'],                                         // 姓名
                'mobile'  => $datas['mobile'],                                       // 手机号
                'city'    => mb_substr($datas['city'], 0, -1),//str_replace('市', '', $datas['city']),     // 城市
                'baodan_is' => $datas['has_insurance'] ? '有' : '无',                 // 寿险保单
                'car'       => $datas['car_info'] == '000' ? '无' : '有',             // 车产
                'house'     => $datas['house_info'] == '000' ? '无' : '有',           // 房子
                'age'     => $datas['age'],                                          // 年龄
                'credit_card' => $datas['has_creditcard'] ? '有' : '无',              // 信用卡
                'source'  => ZhudaiwangConfig::SOURCE,                                // source
                'ip'      => Utils::ipAddress()                                       // ip
            ]
        ];

        // 获取接口
        $url = ZhudaiwangConfig::URL;

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();

        return $result;
    }

    /**
     * 获取信息
     * @param int $code
     * @return mixed|string
     */
    public static function getMessage($code = 0){
        $arr = [
            3 => '指定时间内重复申请(请和运营协商时间)',
            5 => '失败',
            6 => '恶意ip',
            7 => '恶意电话',
        ];
        $message = '';
        if ($code > 1000000) {
            $message = '成功';
        } elseif(isset($arr[$code])) {
            $message = $arr[$code];
        } else {
            $message = '不再接受数据';
        }

        return $message;
    }
}