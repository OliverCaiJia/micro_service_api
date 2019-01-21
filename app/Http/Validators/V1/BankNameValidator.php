<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

class BankNameValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'account' => ['required'],
        'name'    => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'account.required' => '银行账号参数account必填',
        'name.required'    => '银行名称参数name必填',
    );

}