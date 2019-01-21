<?php
namespace App\Models\Chain\Credit;

use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserAccount;

class UpdateAccountAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '对不起,账户增加现金失败！', 'code' => 6005);
    protected $data;

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * @return array|bool
     * 4.账户增加现金
     */
    public function handleRequest()
    {
        if ($this->updateAccount($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 修改用户账户信息
     * 1、账号增加钱（money）
     * （1）balance_frost += money;
     * （2）balance_frost -= money;
     * balance_cash += money;
     * balance = balance_cash + balance_frost;
     * （3）income +=money;
     * total = income - expend;
     * total，balance，frost 不直接操作，通过公式计算得出
     */
    private function updateAccount($params)
    {
        $userId       = $params['userId'];
        $income       = $params['income_money'];
        $income_money = intval($income);
        //锁行
        $userAccountObj = UserAccount::lockForUpdate()->firstOrCreate(['user_id' => $userId], [
            'user_id'      => $userId,
            'balance_cash' => $income_money,
            'balance'      => $income_money,
            'income'       => $income_money,
            'total'        => $income_money,
            'updated_at'   => date('Y-m-d H:i:s', time()),
            'updated_ip'   => Utils::ipAddress(),
        ]);
        //修改
        $userAccountObj->user_id = $userId;
        $userAccountObj->balance_frost += $income_money;
        $userAccountObj->balance_frost -= $income_money;
        $userAccountObj->balance_cash += $income_money;
        $userAccountObj->balance = $userAccountObj->balance_frost + $userAccountObj->balance_cash;
        $userAccountObj->income += $income_money;
        $userAccountObj->total      = $userAccountObj->income - $userAccountObj->expend;
        $userAccountObj->updated_at = date('Y-m-d H:i:s', time());
        $userAccountObj->updated_ip = Utils::ipAddress();
        return $userAccountObj->save();
    }

}




