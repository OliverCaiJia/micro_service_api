<?php

namespace App\Models\Chain\Urge;

use App\Models\Factory\CreditFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Urge\UpdateUrgeAction;

class CheckCreditsDataAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '抱歉，积分不足，无法提交催审~', 'code' => 6001);
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
        if ($this->checkCreditsData($this->params) == true) {
            $this->setSuccessor(new UpdateUrgeAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 传值判断  不能低于用户积分
     */
    private function checkCreditsData($params)
    {
        $userId = $params['user_id'];
        $expend_credits = $params['expend'];
        $userCredits = CreditFactory::fetchCredit($userId);
        if ($userCredits >= $expend_credits && $expend_credits > 0 && $userCredits > 0) {
            return true;
        }
        return false;
    }


}
