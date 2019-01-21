<?php
namespace App\Models\Chain\ProductApply;

use App\Constants\CreditConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserAccountLog;
use App\Models\Orm\UserCreditProductLog;

class CheckAccountLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '申请产品成功资金加入账户流水失败!', 'code' => 8008);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 判断产品申请成功之后资金是否加入账户流水
     */
    public function handleRequest()
    {
        if ($this->checkAccountLog($this->params) == true) {
            $this->setSuccessor(new CreateAccountLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return true;
        }
    }


    /**
     * @param $params
     * 判断产品申请成功之后资金是否加入账户流水
     */
    public function checkAccountLog($params)
    {
        $logCount = UserCreditProductLog::where(['user_id' => $params['userId']])
            ->count();
        if ($logCount > 1) {
            return false;
        }
        return true;
    }
}