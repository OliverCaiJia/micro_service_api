<?php
namespace App\Models\Chain\AddIntegral;

use App\Models\Factory\CreditFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\AddIntegral\UpdateCreditStatusAction;

class UpdateCreditAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '对不起,用户总积分减少失败！', 'code' => 6003);
    protected $data;

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * @return array|bool
     * 3.修改用户总积分
     */
    public function handleRequest()
    {
        if ($this->updateCredit($this->params) == true) {
            $this->setSuccessor(new UpdateCreditStatusAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 更新用户总积分
     * @param $params
     * @return bool
     */
    private function updateCredit($params)
    {
        $params['user_id'] = $params['userId'];
        return CreditFactory::addUserCredit($params);
    }
}
