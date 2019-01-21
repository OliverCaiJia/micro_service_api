<?php

namespace App\Models\Chain\Login;

use App\Models\Orm\UserAuth;
use App\Models\Chain\AbstractHandler;
use DB;

class CheckCodeAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '验证码输入不正确！', 'code' => 9000);

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
        if ($this->checkCode($this->params['mobile'],$this->params['code']) == true)
        {
            $this->setSuccessor(new UpdateLoginTimeAction($this->params));
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
    private function checkCode($mobile, $code)
    {
        #查库
        $phone_cache_key = 'login_phone_code_' . $mobile;
        if(Cache::has($phone_cache_key)){
            if(Cache::get($phone_cache_key) == $code){
                return true;
            }
        }
        return false;
    }

}
