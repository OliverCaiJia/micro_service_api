<?php
namespace App\Services\Core\Oneloan\Rongshidai\RongshidaiConfig;

class RsaUtil {

    private $public_key = ''; // 公钥
    private $private_key = ''; // 私钥
    public static $util;      // 单例对象

    /** 单例构造
     * @return static
     */
    public static function i()
    {
        if (!(self::$util instanceof static))
        {
            self::$util = new static();
        }

        return self::$util;
    }

    /** 构造方法
     * RsaUtil constructor.
     */
    function __construct()
    {
        $this->public_key = file_get_contents('rsa_public_key.pem', 1);
        $this->private_key = file_get_contents('rsa_private_key.pem', 1);
    }

    /** rsa加密算法
     * @param $data
     * @return string
     */
    public function rsaEncrypt($data)
    {
        //公钥内容
        $public_content = $this->public_key;
        //判断公钥的可用性
        $public_key = openssl_get_publickey($public_content);
        //公钥加密
        $res = $this->encrypt($data);
        //释放资源
        openssl_free_key($public_key);
        return $res ? $res : '';
    }

    /** 分段加密
     * @param $originalData
     * @return string
     */
    private function encrypt($data){
        $crypto = '';
        $chunks = str_split($data, 117);
        foreach ($chunks as $chunk) {
            openssl_public_encrypt($chunk, $data, $this->public_key);
            $crypto .= $data;
        }

        return base64_encode($crypto);
    }

    /** sha1withrsa签名
     * @param $data
     * @return string
     */
    public function sha1WithRsaSign($data)
    {
        $key = openssl_pkey_get_private($this->private_key);
        openssl_sign($data, $sign, $key, OPENSSL_ALGO_SHA1);
        $sign = base64_encode($sign);
        return $sign;
    }
}