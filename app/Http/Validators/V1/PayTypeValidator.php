<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 免费查、付费查状态验证
 */

class PayTypeValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'payType' => ['required', 'integer', 'min:1', 'max:2'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'payType.required' => '付款类型必须存在',
        'payType.integer' => '付款类型有误',
        'payType.min' => '付款类型有误',
        'payType.max' => '付款类型有误',

    );

}
