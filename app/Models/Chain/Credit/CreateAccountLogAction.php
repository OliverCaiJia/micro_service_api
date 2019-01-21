<?php


namespace App\Models\Chain\Credit;

use App\Constants\CreditConstant;
use App\Helpers\Utils;
use App\Models\Factory\AccountFactory;
use App\Models\Orm\UserAccountLog;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Credit\UpdateAccountAction;
use App\Strategies\AccountLogStrategy;

class CreateAccountLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,用户账户流水插入数据失败！', 'code' => 6004);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed]
     * 3.用户账户流水插入数据
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
        $userId = $params['userId'];
        //以前的数据
        $userAccountArr = AccountFactory::fetchUserAccountsArray($userId);

        $income_money = isset($params['income_money']) ? $params['income_money'] : 0;
        $expend_money = isset($params['expend_money']) ? $params['expend_money'] : 0;

        //类型
        $type   = CreditConstant::CREDIT_CASH_TYPE;
        $remark = CreditConstant::CREDIT_CASH_REMARK;

        //用户账户流水表 数据处理
        $accountLogArr = AccountLogStrategy::getAccountLogDatas($income_money,$expend_money,$userAccountArr,$userId,$type,$remark);
        return $accountLogArr;
    }

}