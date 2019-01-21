<?php
namespace App\Models\Chain\Cash;

use App\Constants\AccountConstant;
use App\Models\Factory\AccountFactory;
use App\Models\Orm\UserAccountLog;
use App\Models\Chain\AbstractHandler;
use App\Strategies\AccountLogStrategy;
use App\Models\Chain\Cash\CreateAccountCashLogAction;

class CreateAccountLogAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '用户账户流水表插入数据失败', 'code' => 7002);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 2.用户账户流水表插入数据
     */
    public function handleRequest()
    {
        if ($this->createAccountLog($this->params) == true) {
            $this->setSuccessor(new CreateAccountCashLogAction($this->params));
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
        $userId = $params['userId'];
        //以前的数据
        $userAccountArr = AccountFactory::fetchUserAccountsArray($userId);

        $income_money = isset($params['income_money']) ? $params['income_money'] : 0;
        $expend_money = isset($params['money']) ? $params['money'] : 0;
        
        //类型
        $type = AccountConstant::ACCOUNT_CASH_TYPE;
        $remark = AccountConstant::ACCOUNT_CASH_REMARK;
       
        //用户账户流水表 数据处理
        $accountLogArr = AccountLogStrategy::getAccountLogDatas($income_money, $expend_money, $userAccountArr, $userId, $type, $remark);
        return $accountLogArr;
    }


}
