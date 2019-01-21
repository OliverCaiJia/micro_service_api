<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 更换银行卡信息验证器
 */

class BankReplaceValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'account' => ['required', 'regex:/^[0-9]{16,19}$/'],
        'mobile' => ['required', 'regex:/^1[3|4|5|6|7|8|9]\d{9}$/'],
        'cardType' => ['required', 'integer', 'min:1', 'max:2'],
        'replace' => ['required', 'string', 'check_replace'],
        'userbankId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'account.required' => '银行账号参数account必填',
        'account.regex' => '银行账号格式错误',
        'mobile.required' => '手机号必填',
        'mobile.regex' => '手机号格式不正确，请重新输入',
        'cardType.required' => '银行卡类型参数必须存在',
        'cardType.integer' => '银行卡类型参数不正确',
        'cardType.min' => '银行卡类型参数不正确',
        'cardType.max' => '银行卡类型参数不正确',
        'replace.required' => '银行卡更换类型参数必须存在',
        'replace.check_replace' => '不可以进行更换',
        'userbankId.required' => '银行卡更换id参数必须存在',
    );

    public function before()
    {
        // 自定义不是数字的规则
        $this->extend('check_replace', function ($attribute, $value, $parameters) {
            if ($value != 'replace') {
                return false;
            }
            return true;
        });

    }

}
