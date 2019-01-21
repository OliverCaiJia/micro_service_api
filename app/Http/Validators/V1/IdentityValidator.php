<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 * Class LocationValidator
 * @package App\Http\Validators\V1
 * 修改身份验证器
 */
class IdentityValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'identity' => ['required', 'integer', 'min:1', 'max:4'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'identity.required' => '身份参数必须存在',
        'identity.integer' => '身份参数类型错误',
        'identity.min' => '身份参数范围错误',
        'identity.max' => '身份参数范围错误',
    );

}