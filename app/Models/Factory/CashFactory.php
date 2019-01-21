<?php
namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\UserAccountCash;

class CashFactory extends  AbsModelFactory
{
    /**
     * 提现流水
     * 最后10条
     */
    public static function fetchUserAccountCash()
    {
        $userAccountCashArr = UserAccountCash::select(['total','user_id'])
            ->where(['status'=>3])  //status为3 提现成功
            ->orderBy('create_at','desc')
            ->limit(10)->get()->toArray();
        return $userAccountCashArr;
    }
}
