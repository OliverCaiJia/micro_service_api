<?php

namespace App\Models\Chain\Club\Register;

use App\Models\Chain\AbstractHandler;
use App\Services\Core\Club\ClubService;
use App\Models\Chain\Club\Register\CreateUserClubAction;

class ClubRegisterAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '论坛首次注册失败', 'code' => 1001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第一步:调论坛注册接口获取返回值
     * @return array
     */
    public function handleRequest()
    {
        if ($this->clubRegister($this->params) == true)
        {
            $this->setSuccessor(new CreateUserClubAction($this->params));
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
    private function clubRegister($params = [])
    {
        //调论坛注册接口
        $registerData = ClubService::clubRegister($params);
        if ($registerData['code'] != 0)
        {
            $this->error['code'] = $registerData['code'];
            $this->error['error'] = $registerData['msg'];
            return false;
        }
        $registerData['data']['user_id'] = $params['user_id'];
        $this->params['club'] = $registerData['data'];
        return $registerData['data'];
    }

}
