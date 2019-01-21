<?php

namespace App\Http\Validators\V1;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

/*
 *
 * 修改账单金额验证器
 */

class BillMoneyValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'billMoney' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        'billId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'billMoney.required' => '金额必须存在',
        'billMoney.numeric' => '金额不合法',
        'billMoney.min' => '金额不在范围内',
        'billMoney.max' => '金额不在范围内',
        'billId.required' => '账单id必须存在',
        'billId.integer' => '账单id有误',
    );

}