<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 在修改之前查询显示验证规则
 */

class CreditcardAccountIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'accountId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'accountId.required' => '提醒账户必须存在',
        'accountId.integer' => '提醒账户类型错误',
    );

}