<?php
namespace App\Models\Chain\ProductApply;

use App\Constants\CreditConstant;
use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserAccount;
use App\Models\Orm\UserInviteLog;

class UpdateAccountAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '邀请人账户表更新数据失败!', 'code' => 8008);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 8.邀请人账户表更新数据
     */
    public function handleRequest()
    {
        if ($this->updateAccount($this->params) == true) {
            return $this->params['credits'];
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
        $userId       = $params['inviteId'];
        $income       = CreditConstant::PRODUCT_APPLY_MONEY;
        $income_money = $income;

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
        $userAccountObj->expend     = isset($userAccountObj->expend) ? $userAccountObj->expend : 0;
        $userAccountObj->total      = $userAccountObj->income - $userAccountObj->expend;
        $userAccountObj->updated_at = date('Y-m-d H:i:s', time());
        $userAccountObj->updated_ip = Utils::ipAddress();
        return $userAccountObj->save();
    }

}

