<?php

namespace App\Services\Core\Oneloan\Dianrongmojie;

use App\Helpers\Http\HttpClient;
use App\Helpers\Utils;
use App\Services\AppService;
use App\Services\Core\Oneloan\Dianrongmojie\Config\DianrongmojieConfig;
use App\Strategies\SpreadStrategy;
use App\Models\Factory\UserSpreadFactory;
use Illuminate\Support\Facades\Log;

/**
 * 点融魔戒 —— 接口对接Service
 * Class DianrongmojieService
 */
class DianrongmojieService extends AppService
{
    /**
     * 点融魔戒 —— 接口对接Service
     *
     * @param $datas
     * @return array
     */
    public static function spread($datas)
    {
        $token = DianrongmojieConfig::TOKEN;
        // 请求参数
        $request = [
            'form_params' => [
                'ChannelId' => DianrongmojieConfig::CHANNEL,               // CHANNEL
                'SourceId'  => DianrongmojieConfig::SOURCE,                // SOURCE
                'token'     => $token,                                 // TOKEN
                'sign'      => DianrongmojieConfig::getSign($token, $datas['mobile']),// 签名
                'phone'       => $datas['mobile'],                     // 手机号
                'userName'    => $datas['name'],                   // 姓名
                'shenfenzh'   => $datas['certificate_no'],             // 身份证号
                'applyLoanAmount' => $datas['money'],                  //贷款金额
                'applyLoanMonth'  => 7,                                // 贷款期限, 约定默认7
                'daikuanyt'       => '日常消费',                        // 贷款用途
                'loanType'        => '个人贷款',                        // 贷款类型, 非必填约定默认"个人贷款"
                'info'            => DianrongmojieConfig::getInfo($datas)
            ]
        ];
        // 获取url
        $url = DianrongmojieConfig::URL;

        $promise = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $promise->getBody()->getContents();
        $resultObj = json_decode($result, true);

        return $resultObj;
    }
}