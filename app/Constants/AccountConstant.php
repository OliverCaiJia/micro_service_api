<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 账户模块中使用的常量
 */
class AccountConstant extends AppConstant
{
    //状态值
    const SIGN_FULL = 1;
    const SIGN_EMPTY = 0;
    //默认空
    const ACCOUNT_NULL = 0;
    //提现最低值
    const ACCOUNT_CASH = 5;
    //提现
    //积分兑现金
    const ACCOUNT_CASH_REMARK = '提现';
    const ACCOUNT_CASH_TYPE = 'account_cash';
}

