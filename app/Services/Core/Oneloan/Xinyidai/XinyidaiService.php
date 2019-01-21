<?php

namespace App\Services\Core\Oneloan\Xinyidai;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\AppService;
use App\Services\Core\Oneloan\Xinyidai\Config\XinyidaiConfig;
use App\Strategies\SpreadStrategy;
use App\Models\Factory\UserSpreadFactory;
use Illuminate\Support\Facades\Log;

/**
 * 平安新一贷 —— 接口对接Service
 * Class XinyidaiService
 * @package App\Services\Core\Data\Xiaoxiaojinrong
 */
class XinyidaiService extends AppService
{
    /**
     * 平安新一贷 —— 接口对接Service
     *
     * @param array $datas
     * @return array
     */
    public static function spread($datas)
    {
        // 请求参数
        $request = [
            'json' => [
                // 必填
                'userName' => $datas['name'],  // 客户姓名
                'userAge' => '23-55岁',    // 年龄,这是字符串eg:'23-55岁'、23岁以下
                'userPhone' => $datas['mobile'],  // 手机号
                'city' => '上海市-上海市',   //  城市 与甲方约定为 “上海市-上海市”
                'cityCode' => $datas['city_code'],  // 城市编码 todo:做城市编码与城市的映射关系
                'source' => XinyidaiConfig::SOURCE,                             // 投放source
                'outerSource' => XinyidaiConfig::OUTERSOURCE,                   // 投放outSource
                'outerid' => XinyidaiConfig::OUTERID,                   // 投放outerid
                'cid' => XinyidaiConfig::CID,                   // 投放cid
                'userCreditCard' => $datas['has_creditcard'] ? 'YES' : 'NO',    // 有过信用卡
                'liveTime' => '01',                                             // 在该城市已居住时间或工作时间
                'houseLoan' => $datas['house_info'] == '001' ? 'YES' : 'NO',    // 在该城市有过房贷
                'userCar' => $datas['car_info'] == '000' ? 'NO' : 'YES',      // 名下有私家车
                'insurancePolicy' => $datas['has_insurance'] == 0 ? 'NO' : 'YES',    // 购买过寿险保单
            ],
        ];

        // 获取url
        $url = XinyidaiConfig::URL;

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $resultObj = json_decode($result, true);

        return $resultObj;
    }

}