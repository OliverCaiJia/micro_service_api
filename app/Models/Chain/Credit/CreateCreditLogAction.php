<?php


namespace App\Models\Chain\Credit;

use App\Constants\CreditConstant;
use App\Helpers\Utils;
use App\Models\Orm\UserCreditLog;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Credit\UpdateCreditAction;
use App\Strategies\CreditStrategy;

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
     * 1.积分兑换流水插入数据
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
        //积分号
        $nid = CreditStrategy::creditNid();

        $creditLog            = new UserCreditLog;
        $creditLog->nid       = $nid;
        $creditLog->user_id   = $params['userId'];
        $creditLog->type      = CreditConstant::CREDIT_CASH_TYPE;
        $creditLog->income    = 0;
        $creditLog->expend    = $params['expend_credits'];
        $creditLog->credit    = abs(bcsub($creditLog->income, $creditLog->expend));
        $creditLog->remark    = CreditConstant::CREDIT_CASH_REMARK;
        $creditLog->create_at = date('Y-m-d H:i:s', time());
        $creditLog->create_ip = Utils::ipAddress();
        return $creditLog->save();
    }

}