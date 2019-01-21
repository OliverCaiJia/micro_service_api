<?php


namespace App\Models\Chain\Urge;

use App\Constants\CreditConstant;
use App\Models\Factory\CreditFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Urge\UpdateCreditAction;

class CreateCreditLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,积分兑换流水插入数据失败！', 'code' => 6002);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed]
     * 3.积分兑换流水插入数据
     */
    public function handleRequest()
    {
        if ($this->createUserCreditLog($this->params)==true) {
            $this->setSuccessor(new UpdateCreditAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    private function createUserCreditLog($params)
    {
        $params['type'] = CreditConstant::REDUCE_URGE_CREDIT_TYPE;
        $params['remark'] = CreditConstant::REDUCR_URGE_CREDIT_REMARK;
        return CreditFactory::createReduceCreditLog($params);
    }

}