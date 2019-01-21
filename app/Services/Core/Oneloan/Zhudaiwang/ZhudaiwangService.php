<?php

namespace App\Services\Core\Oneloan\Zhudaiwang;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\AppService;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\Zhudaiwang\Config\ZhudaiwangConfig;
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
     * @return int
     */
    public static function spread($datas)
    {
        $request = [
            'form_params' => [
                'name' => $datas['name'],                                         // 姓名
                'mobile' => $datas['mobile'],                                       // 手机号
                'city' => mb_substr($datas['city'], 0, -1),                       // 城市
                'baodan_is' => $datas['has_insurance'] == 0 ? '无' : '有',                 // 寿险保单
                'car' => $datas['car_info'] == '000' ? '无' : '有',             // 车产
                'house' => $datas['house_info'] == '000' ? '无' : '有',           // 房子
                'age' => $datas['age'],                                          // 年龄
                'credit_card' => $datas['has_creditcard'] ? '有' : '无',              // 信用卡
                'source' => ZhudaiwangConfig::SOURCE,                                // source
                'ip' => isset($datas['created_ip']) ? $datas['created_ip'] : Utils::ipAddress(), // ip
            ],
        ];

        // 获取接口
        $url = ZhudaiwangConfig::URL;

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();

        return $result;
    }
}