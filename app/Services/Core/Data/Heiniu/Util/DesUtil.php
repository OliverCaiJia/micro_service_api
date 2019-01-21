<?php
namespace App\Services\Core\Data\Heiniu\Util;

class DesUtil {
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

    private function __clone() {}

    private function __construct() {}

    /**
     * DES加密
     * @param $str
     * @param $key
     * @return string
     */
    function encrypt($str, $key){
        $block = mcrypt_get_block_size('des', 'ecb');
        $pad = $block - (strlen($str) % $block);
        $str .= str_repeat(chr($pad), $pad);
        return base64_encode(mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB));
    }

    /**
     * DES解密
     * @param $sStr
     * @param $sKey
     * @return bool|string
     */
    function decrypt($sStr, $sKey) {
        $decrypted= mcrypt_decrypt(MCRYPT_DES, $sKey, base64_decode($sStr), MCRYPT_MODE_ECB);

        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s-1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }
}


