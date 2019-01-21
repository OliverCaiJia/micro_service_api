<?php

namespace App\Models\Chain\ProductApply;

use App\Constants\CreditConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\AccountFactory;
use App\Models\Orm\UserAccountLog;
use App\Models\Orm\UserInviteLog;
use App\Strategies\AccountLogStrategy;

class CreateAccountLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '邀请人账户流水表插入数据失败!', 'code' => 8007);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 7.邀请人账户流水表插入数据
     */
    public function handleRequest()
    {
        if ($this->createAccountLog($this->params) == true) {
            $this->setSuccessor(new UpdateAccountAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 用户账户流水插入数据
     */
    private function createAccountLog($params)
    {
        //数组处理
        $accountLogArr = $this->getAccountLogData($params);
        $accountLog    = new UserAccountLog();
        $res           = $accountLog->insert($accountLogArr);
        return $res;
    }

    /**
     * @param $params
     * @return array
     * 数据处理
     */
    private function getAccountLogData($params)
    {
        $userId = $params['inviteId'];
        //以前的数据
        $userAccountArr = AccountFactory::fetchUserAccountsArray($userId);

        //产品申请奖励现金
        $income_money = CreditConstant::PRODUCT_APPLY_MONEY;
        $expend_money = isset($params['expend_money']) ? $params['expend_money'] : 0;

        //类型
        $type   = CreditConstant::PRODUCT_APPLY_TYPE;
        $remark = CreditConstant::PRODUCT_APPLY_REMARK;

        //用户账户流水表 数据处理
        $accountLogArr = AccountLogStrategy::getAccountLogDatas($income_money, $expend_money, $userAccountArr, $userId, $type, $remark);
        return $accountLogArr;
    }
}