<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 手机号码验证
 */

class ResetPhoneValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'mobile' => ['required', 'regex:/^1[3|4|5|6|7|8|9]\d{9}$/'],
        'sign' => ['required'],
        'code' => ['required', 'regex:/^[0-9]{4}$/']
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'mobile.required' => '手机号必填',
        'mobile.regex' => '手机号格式不正确，请重新输入',
        'sign.required' => '验签必填',
        'code.required' => '验证码必填',
        'code.regex' => '验证码格式不正确'
    );

    /**
     * Validation codes
     *
     * @var Array
     */
    public $codes = array(
        'mobile.required' => '2001',
        'mobile.regex' => '2002',
        'sign.Required' => '2003',
    );

}
