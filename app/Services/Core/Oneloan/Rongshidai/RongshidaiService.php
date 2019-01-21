<?php

namespace App\Services\Core\Oneloan\Rongshidai;

use App\Services\AppService;
use App\Helpers\Http\HttpClient;
use App\Services\Core\Oneloan\Rongshidai\RongshidaiConfig\RongshidaiConfig;
use App\Services\Core\Oneloan\Rongshidai\RongshidaiConfig\RsaUtil;

/**
 * 融时代对接
 */
class RongshidaiService extends AppService
{
    /**
     * 融时代接口对接
     *
     * @param array $params
     * @return mixed
     */
    public static function spread($params = [])
    {
        //请求url
        $url = RongshidaiConfig::REAL_URL;
        //请求参数
        $req = [
            'source' => RongshidaiConfig::SYS_TYPE,
            'applyLoanList' => [
                //客户概况
                0 => [
                'mobileNumber' => $params['mobile'],  //必填 手机号
                'city' => $params['city'],  //必填 城市
                'name' => $params['name'],  //选填 名字
                //资质信息
                'isHouse' => $params['house_info'] == '000' ? 'N' : 'Y', //必填 有无房产
                'isCar' => $params['car_info'] == '000' ? 'N' : 'Y', //必填 是否名下有车
                'isFund' => $params['accumulation_fund'] == '000' ? 'N' : 'Y', //必填 是否有公积金
                'isSocialSecurity' => $params['social_security'] == 0 ? 'N' : 'Y', //必填 是否有社保
                'isInsurance' => $params['has_insurance'] == 0 ? 'N' : 'Y', //必填 是否有保单
                //借款需求
                'applyAmount' => $params['money'], //必填 贷款金额额度
                ],
            ],
        ];
        $data = json_encode($req);
        //RSA加密
        $encryptData = RsaUtil::i()->rsaEncrypt($data);
        //SHA1WITHRSA加签
        $sign = RsaUtil::i()->sha1WithRsaSign($data);

        $request = [
            'json' => [
                'data' => $encryptData,
                'sign' => $sign,
                'systype' => RongshidaiConfig::SYS_TYPE,
            ],
        ];

        $response = HttpClient::i(['verify' => false])->request('POST', $url, $request);
        $result = $response->getBody()->getContents();

        $arr = json_decode($result, true);
        return $arr;
    }
}

