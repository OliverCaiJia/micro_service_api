<?php

namespace App\Models\Chain\Sns\Register;

use App\Models\Chain\AbstractHandler;
use App\Services\Core\SNS\Libs\SNSService;
use App\Models\Chain\Sns\Register\CreateUserSnsAction;


class SnsRegisterAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '注册SNS用户失败', 'code' => 1001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第一步:注册SNS用户
     * @return array
     */
    public function handleRequest()
    {
        if ($this->registerUserSns($this->params) == true)
        {
            $this->setSuccessor(new CreateUserSnsAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * SNS注册
     */
    private function registerUserSns($params = [])
    {
        // 同步速贷之家用户到SNS论坛
        $params = [
            'nickname' => $params['username'],
            'password' => $params['password'],
            'reg_type' => 'mobile',
            'mobile' => $params['mobile']
        ];

        // 访问SNS注册接口
        $result = SNSService::i()->register($params);

        if ($result['status'] == 1)
        {
            $userinfo = $result['userinfo'];
            $this->params['sns_user_id'] = $userinfo['id']; //SNS用户id
            $this->params['mobile'] = $userinfo['mobile'];  //SNS用户手机号
            return true;
        }
        elseif($result['status'] == 0)
        {
            $this->error['error'] = $result['errMsg'];
            return false;
        }
    }
}
