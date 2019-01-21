<?php

namespace App\Models\Chain\FastRegister;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\AuthFactory;
use App\Models\Factory\UserFactory;
use App\Models\Chain\FastRegister\RenovateTokenAction;

class CreateUserIdentityAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '创建用户身份信息出错！', 'code' => 111);
    protected $data;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->createUserIdentity($this->params) == true)
        {
            $this->setSuccessor(new RenovateTokenAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * @desc 创建用户身份
     * @param $params
     */
    public function createUserIdentity($params)
    {
    	$params['indent'] = $this->params['user']->indent;
        return AuthFactory::createUserIdentity($params);
    }

}
