<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 */

class UserSpreadCheckValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'mobile'  => 'required',
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'mobile.required' => '手机号必须填写!',
    );

}
