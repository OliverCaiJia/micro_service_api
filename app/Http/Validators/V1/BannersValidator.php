<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 获取手机验证码
 */

class BannersValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'adNum' => ['required', 'regex:/^\d+$/'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'adNum.required' => '参数必填',
        'adNum.regex' => '参数格式不正确，请重新输入',
    );

}
