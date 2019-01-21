<?php

namespace App\Services\Core\Platform\JsXianjinxia\Xianjinxia;

use Mockery\Exception;

/**
 * Class RsaUtil
 * @package App\Services\Core\Platform\JsXianjinxia\Xianjinxia
 * 现金侠
 */
class RsaUtil {

    private $public_key = ''; // 公钥
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

    /**
     *  AES加密
     * 使用 AES/CBC/NoPadding 的方式进行加密
     */
    public function AesEncrypt($encryptStr, $localIV = '', $encryptKey = '', $toBase64 = true)
    {
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encryptKey, $encryptStr, MCRYPT_MODE_CBC, $localIV);
        if (!$toBase64) {
            return $encrypted;
        }
        return base64_encode($encrypted);
    }

    /**
     * 签名
     */
    public function fetchSign($data, $secret)
    {
        $signStr = 'data=' . $data;
        $md5SignStr = md5($signStr) . $secret;
        //对拼接后的字符串再次进行 MD5 签名
        $sign = md5($md5SignStr);

        return $sign ? $sign : '';
    }

}