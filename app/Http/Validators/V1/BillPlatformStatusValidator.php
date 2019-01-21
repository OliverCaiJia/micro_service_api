<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 单张信用卡id验证器
 */

class BillPlatformStatusValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'billPlatformId' => ['required', 'integer'],
        'alertStatus' => ['required', 'integer', 'between:0,1'],
        'hiddenStatus' => ['required', 'integer', 'between:0,1'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'billPlatformId.required' => '请选择账单平台进行修改',
        'billPlatformId.integer' => '选择修改账单平台有误',
        'alertStatus.required' => '请选择还款提醒状态',
        'alertStatus.integer' => '还款提醒状态选择错误',
        'alertStatus.between' => '还款提醒状态选择错误',
        'hiddenStatus.required' => '请选择隐藏状态',
        'hiddenStatus.integer' => '隐藏状态选择错误',
        'hiddenStatus.between' => '隐藏状态选择错误',
    );

}
