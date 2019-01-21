<?php

namespace App\Models\Chain\Club\Login;

use App\Models\Chain\AbstractHandler;
use App\Services\Core\Club\ClubService;
use App\Models\Chain\Club\Login\UpdateLoginTimeAction;

class ClubLoginAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '对接论坛登录有问题！', 'code' => 1002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第一步:调论坛登录接口获取返回值
     * @return array
     */
    public function handleRequest()
    {
        if ($this->clubLogin($this->params) == true)
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
     * 调论坛登录接口获取返回值
     */
    private function clubLogin($params = [])
    {
        //调论坛登录接口
        $loginData = ClubService::clubLogin($params);
        if ($loginData['code'] != 0)
        {
            $this->error['code'] = $loginData['code'];
            $this->error['error'] = $loginData['msg'];
            return false;
        }

        $loginData['data']['user_id'] = $params['user_id'];
        $this->params['club'] = $loginData['data'];

        return $loginData;
    }

}
