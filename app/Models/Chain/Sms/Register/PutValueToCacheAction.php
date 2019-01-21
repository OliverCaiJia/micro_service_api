<?php
namespace App\Models\Chain\Sms\Register;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\SmsFactory;

class PutValueToCacheAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '存短信信息进cache失败！', 'code' => 4);
    private $params = array();
    protected $randoms;

    public function __construct($params,$randoms)
    {
        $this->params = $params;
        $this->randoms = $randoms;
    }

    /**
     * @return mixed]
     * 存短信信息进cache
     */
    public function handleRequest()
    {
        if ($this->putValueToCache($this->params,$this->randoms)==true) {
            return $this->randoms;
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 存短信信息进cache
     */
    private function putValueToCache($params,$randoms)
    {
        #存储验证码
        $codeCache = SmsFactory::putSmsCodeToCache('login_phone_code_'.$params['mobile'],$params['code']);
        #存储32位随机字符串
        $signCache = SmsFactory::putSmsCodeToCache('login_random_'.$params['mobile'],$randoms['sign']);
        if($codeCache && $signCache) {
            return true;
        }
        return false;
    }



}