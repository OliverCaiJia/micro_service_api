<?php


namespace App\Models\Chain\Cash;

use App\Helpers\Utils;
use App\Models\Factory\AccountFactory;
use App\Models\Orm\UserAccountCash;
use App\Models\Chain\AbstractHandler;
use App\Strategies\CashStrategy;
use App\Models\Chain\Cash\UpdateAccountAction;


class CreateAccountCashLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '用户提现流水表插入数据失败！', 'code' => 7003);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed]
     * 3.用户提现流水表插入数据
     */
    public function handleRequest()
    {
        if ($this->createAccountCash($this->params) == true) {
            $this->setSuccessor(new UpdateAccountAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }

    }

    /**
     * @param $params
     * 用户提现流水表插入数据
     */
    private function createAccountCash($params)
    {
        // 提现总额
        $account   = $params['account'];
        $total     = $params['money'];
        $fee       = isset($params['fee']) ? $params['fee'] : 0;
        $credited  = $total - $fee;
        
        // 提现记录
        $cashLogObj            = new UserAccountCash();
        $cashLogObj->user_id   = $params['userId'];
        $cashLogObj->nid       = CashStrategy::creditNid();
        $cashLogObj->type_id   = AccountFactory::getAccountTypeData($params['cashType']);//提现账号类型  【1支付宝，2银行卡】
        $cashLogObj->status    = 0;
        $cashLogObj->account   = $account;
        $cashLogObj->bank_id   = (isset($params['bankId']) && !empty($params['bankId'])) ? $params['bankId'] : 1;
        $cashLogObj->bank      = isset($params['bank']) ? $params['bank'] : '';
        $cashLogObj->branch    =isset($params['branch']) ? $params['branch'] : '';
        $cashLogObj->total     = $total;
        $cashLogObj->credited  = $credited;
        $cashLogObj->fee        = $fee;
        $cashLogObj->create_at = date('Y-m-d H:i:s', time());
        $cashLogObj->create_ip = Utils::ipAddress();
        return $cashLogObj->save();
    }
}