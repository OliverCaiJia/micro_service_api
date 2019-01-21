<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 支付订单接口
 */

class PaymentValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'amount' => ['required', 'money'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'amount.required' => '充值金额必填',
        'amount.money' => '充值金额格式不正确',
    );

    /*
     * 自定义验证规则或者扩展Validator类
     */

    public function before()
    {
        // 自定义不是数字的规则
        $this->extend('money', function($attribute, $value, $parameters) {
            if (preg_match("/^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$/", $value))
            {
                return true;
            }
            return false;
        });
    }

}
