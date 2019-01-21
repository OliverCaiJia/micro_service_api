<?php


namespace App\Models\Chain\ShadowSms\Sms\Register;

use App\Models\Factory\AuthFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Sms\Register\CreateEventSmsAction;

class CreateUserAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '创建用户数据失败！', 'code' => 2);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed]
     * 创建用户数据
     */
    public function handleRequest()
    {
        if ($this->createUserData($this->params) == true) {
            $this->setSuccessor(new CreateEventSmsAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }

    }

    /**
     * @param $params
     * 创建用户数据失败
     */
    private function createUserData($params)
    {
        $params['indent'] = 2;
        $user = AuthFactory::createUser($params);

        #修改code标识status字段为1   1获取验证码  0验证码已验证通过
        $authStatus = AuthFactory::updateUserAuthSMSCodeNotValidate($params['mobile']);

        if ($user) {
            $params['sd_user_id'] = $user->sd_user_id;
            $this->params['user_id'] = $user->sd_user_id;
            $identity = AuthFactory::createUserIdentity($params);
        }
        
        if($authStatus && $identity) {
            return true;
        }

        return false;
    }
}