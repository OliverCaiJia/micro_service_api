<?php

namespace App\Models\Chain\Cash;

use App\Models\Factory\AccountFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Cash\CreateAccountLogAction;

class CheckCashDataAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '余额不足', 'code' => 7001);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.判断传值大小
     */
    public function handleRequest()
    {
        if ($this->checkCashData($this->params) == true) {
            $this->setSuccessor(new CreateAccountLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 传值判断  不能高于用户账户额度
     */
    private function checkCashData($params)
    {
        $userId = $params['userId'];
        $money = $params['money'];
        $userBalance = AccountFactory::fetchBalance($userId);
        if ($userBalance >= $money && $money > 0) {
            return true;
        }
        return false;
    }


}
