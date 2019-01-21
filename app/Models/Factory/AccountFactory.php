<?php

namespace App\Models\Factory;

use App\Constants\AccountConstant;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserAccount;
use App\Models\Orm\UserAccountLog;
use App\Models\Orm\UserAccountType;

class AccountFactory extends AbsModelFactory
{

    /** 我的账号余额
     * @param array $data
     * @return mixed
     */
    public static function fetchBalance($userId)
    {
        $account = UserAccount::select(['balance'])->where(['user_id' => $userId])->first();
        return $account ? $account->balance : AccountConstant::ACCOUNT_NULL;
    }

    /**
     * @param $userId
     * 用户账户信息  Array
     */
    public static function fetchUserAccountsArray($userId)
    {
        $userAccountArr = UserAccount::where(['user_id' => $userId])->first();
        return $userAccountArr ? $userAccountArr->toArray() : [];
    }

    /**
     * @param $userId
     * @return UserAccount
     * 用户账户信息   Object
     */
    public static function fetchUserAccountsObj($userId)
    {
        $userAccountObj = UserAccount::where(['user_id' => $userId])->first();
        return $userAccountObj ?: (new UserAccount());
    }

    /**
     * @param $userId
     * 用户账户流水
     */
    public static function fetchUserAccountLog($userId)
    {
        $accountLogArr = UserAccountLog::select(['income', 'expend', 'remark', 'create_at'])
            ->where(['user_id' => $userId])
            ->orderBy('create_at', 'desc')
            ->get()->toArray();
        return $accountLogArr;
    }

    /**
     * @param $nid
     * 查询账户流水类型
     */
    public static function getAccountTypeData($nid)
    {
        $accountType = UserAccountType::select(['id'])
            ->where(['nid' => $nid, 'status' => 1])//status为１　正常
            ->first();
        return $accountType ? $accountType->id : 0;
    }

    /**
     * @param $params
     * @return bool
     * 添加账户流水
     */
    public static function createAccountLog($params)
    {
        $accountLog = new UserAccountLog();
        $res = $accountLog->insert($params);

        return $res ? $res : false;
    }

    /**
     * @param $params
     * 修改用户账户信息（加现金）
     * 1、账号增加钱（money）
     * （1）balance_frost += money;
     * （2）balance_frost -= money;
     * balance_cash += money;
     * balance = balance_cash + balance_frost;
     * （3）income +=money;
     * total = income - expend;
     * total，balance，frost 不直接操作，通过公式计算得出
     */
    public static function AddAccount($params)
    {
        $userId = $params['userId'];
        $income = $params['income_money'];
        $income_money = intval($income);
        //锁行
        $userAccountObj = UserAccount::lockForUpdate()->firstOrCreate(['user_id' => $userId], [
            'user_id' => $userId,
            'balance_cash' => $income_money,
            'balance' => $income_money,
            'income' => $income_money,
            'total' => $income_money,
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
        ]);
        //修改
        $userAccountObj->user_id = $userId;
        $userAccountObj->balance_frost += $income_money;
        $userAccountObj->balance_frost -= $income_money;
        $userAccountObj->balance_cash += $income_money;
        $userAccountObj->balance = $userAccountObj->balance_frost + $userAccountObj->balance_cash;
        $userAccountObj->income += $income_money;
        $userAccountObj->total = $userAccountObj->income - $userAccountObj->expend;
        $userAccountObj->updated_at = date('Y-m-d H:i:s', time());
        $userAccountObj->updated_ip = Utils::ipAddress();
        return $userAccountObj->save();
    }

    /**
     * @return array
     * 查询账户income 小于0的数据
     */
    public static function fetchAccounts()
    {
        $account = UserAccount::select()
            ->where('income', '<', 0)
            ->orWhere('total', '<', 0)
            ->limit(100)
            ->get()->toArray();

        return $account ? $account : [];
    }
}
