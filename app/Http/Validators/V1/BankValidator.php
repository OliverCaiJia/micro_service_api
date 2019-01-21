<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

class BankValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'account' => ['required','regex:/^[0-9]{16,19}$/'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'account.required' => '银行账号参数account必填',
        'account.regex' => '银行账号格式错误',
    );

}