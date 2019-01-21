<?php

namespace App\Services\Core\Platform\Jiufuqianbao\Jiufudingdang;

use Mockery\Exception;

/**
 * Class RsaUtil
 * @package App\Services\Core\Platform\Jiufuqianbao\Jiufudingdang
 * 玖富叮当贷
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

    /** 构造方法
     * RsaUtil constructor.
     */
    function __construct()
    {
        $this->public_key = file_get_contents('rsa_public_key.pem', 1);
    }

    /**
     * @param $data
     * @return string
     * RSA加密
     */
    public  function rsaEncrypt($data)
    {
        //公钥内容
        $public_content = $this->public_key;
        //判断公钥的可用性
        $public_key = openssl_get_publickey($public_content);
        //待加密的数据
        $original_str = $data;
        //公钥加密
        openssl_public_encrypt($original_str, $encrypted, $public_key);//公钥加密
        $encrypted = base64_encode($encrypted);

        return $encrypted ? $encrypted : '';
    }


}