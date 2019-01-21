<?php

namespace App\Services\Core\Platform\JsXianjinxia\Xianjinxia;

use App\Services\Core\Data\Hengchang\Config\HengchangConfig;
use Mockery\Exception;

/**
 * Class RsaUtil
 * @package App\Services\Core\Platform\JsXianjinxia\Xianjinxia
 * 现金侠
 */
class AesUtil {
    public static $util;      // 单例对象
    public $iv = null;
    public $key = null;
    public $bit = 128;
    private $cipher;

    /** 单例构造
     * @return static
     */
    public static function i($bit, $key, $iv, $mode)
    {
        if (!(self::$util instanceof static))
        {
            self::$util = new static($bit, $key, $iv, $mode);
        }

        return self::$util;
    }

    /*
     * 私有化克隆函数
     */
    private function __clone() {}

    private function __construct($bit, $key, $iv, $mode) {
        if(empty($bit) || empty($key) || empty($iv) || empty($mode))
            return NULL;

        $this->bit = $bit;
        $this->key = $key;
        $this->iv = $iv;
        $this->mode = $mode;

        switch($this->bit) {
            case 192:$this->cipher = MCRYPT_RIJNDAEL_192; break;
            case 256:$this->cipher = MCRYPT_RIJNDAEL_256; break;
            default: $this->cipher = MCRYPT_RIJNDAEL_128;
        }

        switch($this->mode) {
            case 'ecb':$this->mode = MCRYPT_MODE_ECB; break;
            case 'cfb':$this->mode = MCRYPT_MODE_CFB; break;
            case 'ofb':$this->mode = MCRYPT_MODE_OFB; break;
            case 'nofb':$this->mode = MCRYPT_MODE_NOFB; break;
            default: $this->mode = MCRYPT_MODE_CBC;
        }
    }

    /**
     * 加密
     * @param $data
     * @return string
     */
    public function encrypt($data) {
        $data = base64_encode(mcrypt_encrypt( $this->cipher, $this->key, $data, $this->mode, $this->iv));
        return $data;
    }

    /**
     * 解密
     * @param $data
     * @return string
     */
    public function decrypt($data) {
        $data = mcrypt_decrypt( $this->cipher, $this->key, base64_decode($data), $this->mode, $this->iv);
        $data = rtrim(rtrim($data), "\x00..\x1F");
        return $data;
    }

}