<?php

namespace App\Services\Core\Payment\YiBao;

use App\Helpers\Http\HttpClient;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\PaymentFactory;
use App\Services\Core\Payment\PaymentService;

class YiBaoService extends PaymentService
{
    /**
     * 银行卡信息查询
     *
     * $params = [
     *      'cardno' => '',  卡号
     * ]
     * @param array $params 参数数组
     * @return array|mixed|string
     */
    public function bankCardInfo($params = [])
    {
        if(!is_array($params))
        {
            return '参数必须是数组！';
        }
        $url = YiBaoConfig::YIBAO_URL .'/payapi/api/bankcard/check';
        $data = self::getParams($params);
        $request = [
            'form_params' => $data
        ];

        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);

        //对返回结果进行解码
        if(isset($res['error_code']))
        {
            return [];
        }

        //对返回结果进行解码
        $arr = self::undoData($res['data'], $res['encryptkey']);
        if(!is_array($arr))
        {
            return [];
        }

        return $arr;

    }

    /**
     * 订单退款接口
     *
     * $params = [
     *      'orderid' =>'', 退款请求号,订单号
     *      'origyborderid' => '', 易宝流水号
     *      'amount' => 1,   退款金额
     *      'currency' => 156,  交易币种
     *      'cause' => '',  退款说明
     * ]
     * @param array $params 参数数组
     * @return array|mixed|string
     */
    public function orderRefund($params = [])
    {
        if(!is_array($params))
        {
            return '参数必须是数组！';
        }
        $url = YiBaoConfig::YIBAO_URL .'/merchant/query_server/direct_refund';
        $data = self::getParams($params);

        $request = [
            'form_params' => $data
        ];

        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);
        //对返回结果进行解码
        if(isset($res['error_code']))
        {
            return [];
        }

        //对返回结果进行解码
        $arr = self::undoData($res['data'], $res['encryptkey']);
        if(!is_array($arr))
        {
            return [];
        }

        return $arr;
    }

    /**
     * 订单查询接口
     *
     * $params = [
     *      'orderid' => '', 商户订单号
     * ]
     * @param array $params 参数数组
     * @return array|mixed|string
     */
    public function orderQuery($params = [])
    {
        if(!is_array($params))
        {
            return '参数必须是数组！';
        }
        $url = YiBaoConfig::YIBAO_URL .'/merchant/query_server/pay_single';
        $data = self::getParams($params);
        $request = [
            'query' => $data
        ];

        $response = HttpClient::i()->request('GET', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);
        //对返回结果进行解码
        if(isset($res['error_code']))
        {
            return [];
        }

        //对返回结果进行解码
        $arr = self::undoData($res['data'], $res['encryptkey']);
        if(!is_array($arr))
        {
            return [];
        }

        return $arr;
    }

    /**
     * 订单支付接口
     *
     * @param array $params 参数数组 注意：参数数组中的数据类型
     * @return array|mixed|string
     */
    public function orderPay($params = [])
    {
        if(!is_array($params))
        {
            return '参数必须是数组！';
        }
        $url = YiBaoConfig::YIBAO_URL .'/paymobile/payapi/request';
        $data = self::getParams($params);

        $request = [
            'form_params' => $data
        ];

        $response = HttpClient::i()->request('POST', $url, $request);
        $result = $response->getBody()->getContents();
        $res = json_decode($result, true);
//        SLogger::getStream()->info('orderPay',['message'=>$res,'code'=>1001]);
        //对返回结果进行解码
        if(isset($res['error_code']))
        {
            return [];
        }

        $arr = self::undoData($res['data'], $res['encryptkey']);
//        SLogger::getStream()->info('orderPayUndo',['message'=>$arr,'code'=>1002]);
        if(!is_array($arr))
        {
            return [];
        }

        return $arr;
    }

    /**
     * 解密数据
     *
     * @param string $data 接口返回的data数据
     * @param string $encryptkey 接口返回的encryptkey数据
     * @return mixed|string
     */
    public static function undoData($data ,$encryptkey)
    {
        //获取解密的码
        $AESKey = YiBaoConfig::getYeepayAESKey($encryptkey ,PaymentFactory::getYibaoMerchantPrivateKey(self::$nid));

        $return = YiBaoConfig::AESDecryptData($data, $AESKey);
        $return = json_decode($return, true);

        if (!array_key_exists('sign', $return))
        {
            if (array_key_exists('error_code', $return))
            {
                return $return['error_msg']. '-不存在sign-' .$return['error_code'];
            }
        } else {
            if (!YiBaoConfig::RSAVerify($return, $return['sign'], PaymentFactory::getYibaoPublicKey(self::$nid)))
            {
                return '请求返回签名验证失败';
            }
        }
        if (array_key_exists('error', $return))
        {
            return $return['error'].$return['error_code'];
        } elseif(array_key_exists('error_msg', $return)) {
            return $return['error_msg'].$return['error_code'];
        }

        return $return;

    }

    /**
     * 获取最终加密参数数组
     *
     * @param array $params 参数数组
     * @return mixed
     */
    public static function getParams($params = [])
    {
        $account = PaymentFactory::getYibaoMerchantCode(self::$nid);
        if(!array_key_exists('merchantaccount', $params))
        {
            $params['merchantaccount'] = $account;
        }
        //生成签名
        $params['sign'] = YiBaoConfig::RSASign($params ,PaymentFactory::getYibaoMerchantPrivateKey(self::$nid));
        //生成参数encryptkey
        $AESKey = YiBaoConfig::generateAESKey();
        $arr['encryptkey'] = YiBaoConfig::getEncryptkey($AESKey ,PaymentFactory::getYibaoPublicKey(self::$nid));

        //生成参数merchantaccount
        $arr['merchantaccount'] = $account;

        //生成参数data
        $arr['data'] = YiBaoConfig::AESEncryptRequest($AESKey, $params);

        return $arr;
    }


}