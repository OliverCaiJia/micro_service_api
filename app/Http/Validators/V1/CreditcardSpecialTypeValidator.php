<?php

namespace App\Http\Validators\V1;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

/*
 *
 * 创建或修改账单验证器
 */

class CreditcardSpecialTypeValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'specialType' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'specialType.required' => '特色精选类型参数必须存在',
    );

}