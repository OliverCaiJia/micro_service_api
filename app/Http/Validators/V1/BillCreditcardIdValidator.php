<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 单张信用卡id验证器
 */

class BillCreditcardIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'creditcardId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'creditcardId.required' => '请选择信用卡进行修改',
        'creditcardId.integer' => '选择修改信用卡有误',
    );

}
