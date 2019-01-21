<?php

namespace App\Models\Chain\FastRegister;

use App\Models\Factory\AuthFactory;
use App\Models\Orm\UserAuth;
use App\Models\Chain\AbstractHandler;
use DB;
use Cache;

class CheckCodeAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '请输入正确的验证码！', 'code' => 9000);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第二步:验证码是否正确
     * @return array
     */
    public function handleRequest()
    {
        if ($this->checkCode($this->params['mobile'],$this->params['code'],$this->params['sign']) == true)
        {
	        $this->setSuccessor(new CreateUserAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 检查验证码是否正确
     */
    private function checkCode($mobile, $code ,$sign)
    {
        #查库
        $code_cache_key = 'login_phone_code_' . $mobile;
        $sign_cache_key = 'login_random_' . $mobile;
        if(Cache::has($code_cache_key) && Cache::has($sign_cache_key))
        {
            if(Cache::get($code_cache_key) == $code  && Cache::get($sign_cache_key) == $sign)
            {
                //修改code标识转为0
                AuthFactory::updateUserAuthSMSCodeIsValidate($mobile);
                return true;
            }
        }
        return false;
    }

}
