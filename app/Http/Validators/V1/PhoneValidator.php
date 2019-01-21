<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 手机号码验证
 */

class PhoneValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'phone' => ['required', 'regex:/^1[3|4|5|6|7|8|9]\d{9}$/'],
        'code' => ['required', 'regex:/^[0-9]{4}$/']
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'phone.required' => '手机号必填',
        'phone.regex' => '手机号格式不正确，请重新输入',
        'code.required' => '验证码必填',
        'code.regex' => '验证码格式不正确'
    );

}
