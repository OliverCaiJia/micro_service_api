<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 用户银行卡id验证
 */

class UserbankIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'userbankId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'userbankId.required' => '用户银行卡id参数必须存在',
        'userbankId.integer' => '用户银行卡id参数格式错误',
    );
}
