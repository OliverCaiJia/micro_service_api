<?php

namespace App\Http\Validators\V4;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 计算器传值验证器
 */

class CalculatorValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'loanMoney' => ['required', 'string'],
        'loanTimes' => ['required', 'string'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'loanMoney.required' => '借款金额参数必须存在',
        'loanTimes.required' => '借款期限参数必须存在',
        'loanTimes.integer' => '借款期限参数必须是整数',
    );


}
