<?php

namespace App\Models\Chain\Credit;

use App\Models\Factory\CreditFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Credit\CreateCreditLogAction;

class CheckCreditsDataAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '兑换失败，可能积分不足，或请联系客服', 'code' => 6001);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 0.判断传值大小
     */
    public function handleRequest()
    {
        if ($this->checkCreditsData($this->params) == true) {
            $this->setSuccessor(new CreateCreditLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 传值判断  不能低于用户积分
     */
    private function checkCreditsData($params)
    {
        $userId = $params['userId'];
        $expend_credits = $params['expend_credits'];
        $userCredits = CreditFactory::fetchCredit($userId);
        if ($userCredits >= $expend_credits && $expend_credits > 0 && $userCredits > 0) {
            return true;
        }
        return false;
    }


}
