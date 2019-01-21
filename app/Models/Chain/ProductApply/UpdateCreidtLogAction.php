<?php
namespace App\Models\Chain\ProductApply;

use App\Constants\CreditConstant;
use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserCreditLog;
use App\Strategies\CreditStrategy;
use App\Models\Chain\ProductApply\UpdateCreditAction;

class UpdateCreidtLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '积分流水表加积分失败!', 'code' => 8004);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 3.积分流水表加积分
     */
    public function handleRequest()
    {
        if ($this->updateCreditLog($this->params) == true) {
            $this->setSuccessor(new UpdateCreditAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 积分流水表加积分
     */
    private function updateCreditLog($params)
    {
        //积分号
        $nid = CreditStrategy::creditNid();

        $logObj = new UserCreditLog();

        $logObj->nid       = $nid;
        $logObj->user_id   = $params['userId'];
        $logObj->type      = CreditConstant::PRODUCT_APPLY_TYPE;
        $logObj->income    = $params['credits'];
        $logObj->expend    = 0;
        $logObj->credit    = abs(bcsub($logObj->income, $logObj->expend));
        $logObj->remark    = CreditConstant::PRODUCT_APPLY_REMARK;
        $logObj->create_at = date('Y-m-d H:i:s', time());
        $logObj->create_ip = Utils::ipAddress();
        return $logObj->save();
    }

}