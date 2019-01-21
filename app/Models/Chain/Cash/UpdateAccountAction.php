<?php
namespace App\Models\Chain\Cash;

use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserAccount;

class UpdateAccountAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '对不起,账户增加现金失败！', 'code' => 7004);
    protected $data;

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * @return array|bool
     * 4.用户账户表减现金值
     */
    public function handleRequest()
    {
        if ($this->updateAccount($this->params) == true) {
            unset($this->params['userId']);
            $this->setSuccessData($this->params);
            return $this->getSuccessData();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 修改用户账户信息
     * 2、账号出去钱（money）
     * (1)frost_cash +=money;
     * 或frost_other +=money;
     * frost = frost_cash + frost_other;
     * (2)balance_cash -=money;
     * balance = balance_cash + balance_frost;
     * （3）expend += money;
     * total = income - expend;
     *
     * total，balance，frost 不直接操作，通过公式计算得出
     */
    private function updateAccount($params)
    {
        $userId       = $params['userId'];
        $expend_money = intval($params['money']);

        //锁行
        $userAccountObj = UserAccount::where(['user_id' => $userId])->lockForUpdate()->first();
        //修改
        $userAccountObj->user_id = $userId;
        $userAccountObj->balance_frost += $expend_money;
        $userAccountObj->balance_frost -= $expend_money;
        $userAccountObj->balance_cash -= $expend_money;
        $userAccountObj->balance = $userAccountObj->balance_frost + $userAccountObj->balance_cash;
        $userAccountObj->expend += $expend_money;
        $userAccountObj->total      = $userAccountObj->income - $userAccountObj->expend;
        $userAccountObj->updated_at = date('Y-m-d H:i:s', time());
        $userAccountObj->updated_ip = Utils::ipAddress();
        return $userAccountObj->save();
    }

}




