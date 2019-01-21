<?php
namespace App\Models\Chain\UserPush;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CreditFactory;

class UpdateCreditAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '推送加积分，修改用户积分', 'code' => 2203);


    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.修改用户积分 —— 加积分
     */
    public function handleRequest()
    {
        if ($this->updateCredit($this->params) == true) {
            $this->setSuccessor(new CreateEventLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 判断信用资料的完善程度
     */
    private function updateCredit($params)
    {
        $credit = CreditFactory::addUserCredit($params);

        return $credit;
    }


}
