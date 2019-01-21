<?php

namespace App\Services\Core\Payment\YiBao;

use App\Services\Core\Payment\PaymentService;

class YiBaoConfig extends PaymentService
{
   //易宝接口地址
    const YIBAO_URL = 'https://ok.yeepay.com';


    /**
     * 使用易宝公钥检测易宝返回数据签名是否正确
     *
     * @param array $return
     * @param string $sign
     * @return boolean
     */
    public static function RSAVerify($return ,$sign ,$yeepayPublicKey){
        if(array_key_exists('sign', $return))
            unset($return['sign']);
        ksort($return);
        foreach ($return as $k=>$val){
            if( is_array($val) )
                $return[$k] = self::cn_json_encode($val);
        }
        $str = chunk_split($yeepayPublicKey, 64, "\n");
        $private_key ="-----BEGIN PUBLIC KEY-----\n$str-----END PUBLIC KEY-----\n";
        $ok=openssl_verify(join('',$return) , base64_decode($sign) , $private_key , OPENSSL_PKCS1_PADDING);

        return $ok;
    }

    /**
     * uncode 解码
     *
     * @param $value
     * @return mixed|string
     */
    public static function cn_json_encode($value){
        if (defined('JSON_UNESCAPED_UNICODE'))
            return json_encode($value,JSON_UNESCAPED_UNICODE);
        else{
            $encoded = urldecode(json_encode(self::array_urlencode($value)));
            return preg_replace(array('/\r/','/\n/'), array('\\r','\\n'), $encoded);
        }
    }

    /**
     * 对数组
     *
     * @param $value
     * @return array|string
     */
    public static function array_urlencode($value){
        if (is_array($value)) {
            return array_map(array('yeepayMPay','array_urlencode'),$value);
        }elseif (is_bool($value) || is_numeric($value)){
            return $value;
        }else{
            return urlencode(addslashes($value));
        }
    }

    /**
     * 用RSA 签名请求,用商户秘钥生成签名
     *
     * @param array $query 请求参数数组
     * @param string $merchantPrivateKey 易宝后台私钥
     * @return string
     */
    public static function RSASign($query ,$merchantPrivateKey){
        if(array_key_exists('sign', $query))
            unset($query['sign']);
        ksort($query);
        $str = chunk_split($merchantPrivateKey, 64, "\n");
        $private_key = "-----BEGIN PRIVATE KEY-----\n$str-----END PRIVATE KEY-----\n";
        $signature = '';
        if (openssl_sign(join('',$query), $signature, $private_key, OPENSSL_PKCS1_PADDING))
        {
            $sign=base64_encode($signature);
        }

        return $sign;
    }

    /**
     * 通过AES解密易宝返回的数据
     *
     * @param string $data  易宝返回数据中的data参数
     * @param string $AESKey   getYeepayAESKey方法中生成的值
     * @return mixed
     */
    public static function AESDecryptData($data,$AESKey)
    {
        $json=openssl_decrypt(base64_decode($data) ,"aes-128-ecb" ,$AESKey ,OPENSSL_PKCS1_PADDING);

        return preg_replace('/:(\d{11,})(\,|\})/', ':"$1"$2', $json);
    }

    /**
     * 通过AES加密请求数据
     *
     * @param array $query  请求的参数数组
     * @param string $AESKey  generateAESKey方法生成的值
     * @return string
     */
    public static function AESEncryptRequest($AESKey ,$query)
    {
        $ciphertext = base64_encode(openssl_encrypt(json_encode($query) ,"aes-128-ecb" ,$AESKey ,OPENSSL_PKCS1_PADDING));

        return $ciphertext;
    }

    /**
     * 返回易宝返回数据的AESKey
     *
     * @param string $encryptkey  通过使用易宝公钥加密返回的值（getEncryptkey）
     * @param string $merchantPrivateKey  易宝后台私钥
     * @return mixed
     */
    public static function getYeepayAESKey($encryptkey ,$merchantPrivateKey)
    {
        $str = chunk_split($merchantPrivateKey, 64, "\n");
        $private_key = "-----BEGIN PRIVATE KEY-----\n$str-----END PRIVATE KEY-----\n";
        openssl_private_decrypt(base64_decode($encryptkey),$yeepayAESKey,$private_key);

        return $yeepayAESKey;
    }

    /**
     * 通过RSA，使用易宝公钥，加密本次请求的AESKey(是最终提交参数中的encryptkey)
     *
     * @param string  $AESKey  随机生成的16位秘钥
     * @param string $yeepayPublicKey 易宝公钥
     * @return string
     */
    public static function getEncryptkey($AESKey ,$yeepayPublicKey)
    {
        $str = chunk_split($yeepayPublicKey, 64, "\n"); //截取公钥长度64位
        $key = "-----BEGIN PUBLIC KEY-----\n$str-----END PUBLIC KEY-----\n";
        openssl_public_encrypt($AESKey ,$encrypted ,$key ,OPENSSL_PKCS1_PADDING);//公钥加密
        $encryptKey = base64_encode($encrypted);

        return $encryptKey;
    }

    /**
     * 生成一个随机的字符串作为AES密钥
     *
     * @param  int $length 长度
     * @return string
     */
    public static function generateAESKey($length = 16)
    {
        $baseString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $AESKey = '';
        $_len = strlen($baseString);
        for($i=1;$i<=$length;$i++){
            $AESKey .= $baseString[rand(0, $_len-1)];
        }

        return $AESKey;
    }

}
