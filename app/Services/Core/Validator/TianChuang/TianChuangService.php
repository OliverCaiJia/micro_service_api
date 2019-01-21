<?php

namespace App\Services\Core\Validator\TianChuang;

use App\Helpers\Http\HttpClient;
use App\Services\Core\Validator\ValidatorService;

/**
 * 天创
 *
 * Class TianChuangService
 * @package App\Services\Core\Credit\TianChuang
 */
class TianChuangService extends ValidatorService
{

    /**
     * 银行卡号、姓名、身份证号、手机号四要素认证
     *
     * @param array $data
     * $data = [
     *      'bankcard' => $bankcard,  //银行卡号
     *      'name' => $name,   //姓名
     *      'idcard' => $idcard,   //身份证号
     *      'mobile' => $mobile,  //银行预留手机号码
     * ]
     * @return mixed
     */
    public static function authFourthElements($data = [])
    {
        $url = ValidatorService::TIANCHUANG_API_URL . '/bankcard/verifyBankcardInfo4';
        $appid = ValidatorService::getTianChuangAppid();
        $tokenid = ValidatorService::getTianChuangTokenid();
        $tokenKey = self::getTokenKey($url, $tokenid, $data);
        $data['appId'] = $appid;
        $data['tokenKey'] = $tokenKey;

        $request = [
            'form_params' => $data
        ];
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);

        return $res;
    }

    /**
     * 银行卡号、姓名、身份证号三要素认证
     *
     * @param array $data
     * $data = [
     *      'bankcard' => $bankcard, //银行卡号
     *      'name' => $name, //姓名
     *      'idcard' => $idcard,  //身份证号
     * ]
     * @return mixed
     */
    public static function authThirdElements($data = [])
    {
        $url = ValidatorService::TIANCHUANG_API_URL . '/bankcard/verifyBankcardInfo3';
        $appid = ValidatorService::getTianChuangAppid();
        $tokenid = ValidatorService::getTianChuangTokenid();
        $tokenKey = self::getTokenKey($url, $tokenid, $data);
        $data['appId'] = $appid;
        $data['tokenKey'] = $tokenKey;

        $request = [
            'form_params' => $data
        ];
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);

        return $res;
    }

    /**
     * 信用卡鉴权-重发短信验证码
     * 接口描述:如果短信验证码失效或过期,可通过该接又进行重发短验操作
     *
     * @param string $seqNum 鉴权接又返回的流水号seqNum
     * @return mixed
     */
    public static function authCreditCardResendSms($seqNum)
    {
        $url = ValidatorService::TIANCHUANG_API_URL . '/bankcard/reSendCode';
        $appid = ValidatorService::getTianChuangAppid();
        $tokenid = ValidatorService::getTianChuangTokenid();
        $params = [
            'orderId' => $seqNum,
        ];
        $tokenKey = self::getTokenKey($url, $tokenid, $params);
        $params['appId'] = $appid;
        $params['tokenKey'] = $tokenKey;

        $request = [
            'form_params' => $params
        ];
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);

        return $res;
    }

    /**
     * 信用卡鉴权-短信验证
     *
     * @param string $seqNum 鉴权接口返回的流水号seqNum
     * @param string $code  短信验证码
     * @return mixed
     */
    public static function authCreditCardSms($seqNum, $code)
    {
        $url = ValidatorService::TIANCHUANG_API_URL . '/bankcard/verifyCode';
        $appid = ValidatorService::getTianChuangAppid();
        $tokenid = ValidatorService::getTianChuangTokenid();
        $params = [
            'orderId' => $seqNum,
            'code' => $code,
        ];
        $tokenKey = self::getTokenKey($url, $tokenid, $params);
        $params['appId'] = $appid;
        $params['tokenKey'] = $tokenKey;

        $request = [
            'form_params' => $params
        ];
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);

        return $res;
    }

    /**
     * 信用卡四、六项鉴权
     *
     * 自测：目前好像只支持六项,测四项的时候返回信息异常
     *
     * @param $data
     * $data = [
     *      'name' => $data['name'],  //姓名(可选参数)
     *      'idcard' => $data['idcard'],  //身份证号(可选参数)
     *      'creditCard' => $data['creditCard'],     //信用卡号
     *      'mobile' => $data['mobile'],      //银行预留手机号码
     *      'endDate' => $data['endDate'],   //有效期,yyyy-MM
     *      'cvv' => $data['cvv'],    //信用卡验证码,3位数字,卡背面CVV2
     *      'type' => $data['type'],    //是否下发短信验证码 0-不发送 1-发送
     * ]
     * @return mixed
     */
    public static function authCreditCardFourOrSix($data = [])
    {
        $url = ValidatorService::TIANCHUANG_API_URL . '/bankcard/verifyCreditCard';
        $appid = ValidatorService::getTianChuangAppid();
        $tokenid = ValidatorService::getTianChuangTokenid();
        $tokenKey = self::getTokenKey($url, $tokenid, $data);
        $data['appId'] = $appid;
        $data['tokenKey'] = $tokenKey;

        $request = [
            'form_params' => $data
        ];
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);

        return $res;
    }

    /**
     * 身份认证
     *
     * @param string $idCard 身份证号
     * @param string $name  姓名
     * @return mixed
     */
    public static function authPersonalIdCard($idCard, $name)
    {
        $url = ValidatorService::TIANCHUANG_API_URL . '/identity/verifyIdcardC';
        $appid = ValidatorService::getTianChuangAppid();
        $tokenid = ValidatorService::getTianChuangTokenid();
        $params = [
            'idcard' => $idCard,
            'name' => $name,
        ];
        $tokenKey = self::getTokenKey($url, $tokenid, $params);
        $request = [
            'form_params' => [
                'appId' => $appid,
                'tokenKey' => $tokenKey,
                'idcard' => $idCard,
                'name' => $name,
            ]
        ];
        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);

        return $res;
    }

    /**
     * 生成tokenKey
     *
     * @param string $url
     * @param string $tokenid
     * @param $params
     * @return string
     */
    public static function getTokenKey($url = '', $tokenid = '', $params)
    {
        ksort($params);
        $paramStr = '';
        foreach ($params as $key => $param)
        {
            $paramStr .= $key . '=' . $param . ',';
        }

        $paramStr = rtrim($paramStr, ",");

        $tokenKey = md5($url . $tokenid . $paramStr);

        return $tokenKey;
    }

}
