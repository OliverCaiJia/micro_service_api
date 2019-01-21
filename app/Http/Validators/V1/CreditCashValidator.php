<?php

namespace App\Http\Validators\V1;

use App\Constants\CreditConstant;
use App\Http\Validators\AbstractValidator;

/*
 *
 * 积分接口
 */

class CreditCashValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'expend_credits' => ['required', 'integer', 'check_credit'],
        'income_money' => ['required', 'integer', 'check_money'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'expend_credits.required' => '积分参数必须存在',
        'expend_credits.integer' => '积分参数必须是整数',
        'income_money.required' => '兑换现金值必须存在',
        'income_money.integer' => '兑换现金值必须是整数',
    );

    public function before()
    {
        // 自定义积分值必须等于1500
        $this->extend('check_credit', function ($attribute, $value, $parameters) {
            if ($value >= 0 && $value == CreditConstant::EXCHANGE_CREDIT) {
                return true;
            }
            return false;
        });

        // 自定义兑换现金必须为4
        $this->extend('check_money', function ($attribute, $value, $parameters) {
            if ($value >= 0 && $value == CreditConstant::EXCHANGE_MONRY) {
                return true;
            }
            return false;
        });

    }

}
