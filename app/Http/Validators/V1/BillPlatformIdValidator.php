<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 单张信用卡id验证器
 */

class BillPlatformIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'billPlatformId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'billPlatformId.required' => '请选择账单平台进行修改',
        'billPlatformId.integer' => '选择修改账单平台有误',
    );

}
