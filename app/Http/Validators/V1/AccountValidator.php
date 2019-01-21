<?php
namespace App\Http\Validators\V1;

use App\Constants\AccountConstant;
use App\Http\Validators\AbstractValidator;

/*
 *
 * 账户接口
 */

class AccountValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'money'    => ['required', 'integer', 'min_money'],
        'account'  => ['required'],
        'cashType' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'money.required'    => '提现金额必须存在',
        'money.integer'     => '提现金额必须是整数',
        'money.min_money'   => '最低5元提现',
        'account.required'  => '提现账号必须存在',
        'cashType.required' => '提现账号类型必须存在',
    );

    /**
     * Validation codes
     *
     * @var Array
     */
    public $codes = array(
        'money.required'    => '7001',
        'money.integer'     => '7002',
        'money.min_money'   => '7003',
        'account.required'  => '7004',
        'cashType.required' => '7005',
    );

    public function before()
    {
        // 自定义规则检查提现金额
        $this->extend('min_money', function ($attribute, $value, $parameters) {
            if (preg_match("/^[1-9]+[0-9]*$/u", $value)) {
                if ($value >= AccountConstant::ACCOUNT_CASH) {
                    return true;
                }
            }
            return false;
        });

    }
}