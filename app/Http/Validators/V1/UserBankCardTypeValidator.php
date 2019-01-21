<?php

namespace App\Http\Validators\V1;
use App\Http\Validators\AbstractValidator;


class UserBankCardTypeValidator extends AbstractValidator {


    /**
     * Validation rules
     *
     * @var Array
     * android 15位 ， ios 32位 ， h5 28位
     */
    protected $rules = array(
        'type' => ['required','integer', 'min:0', 'max:2'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'type.required' => 'type类型必须存在',
        'type.integer' => 'type必须为整型',
        'type.min' => 'type取值：0,1,2',
        'type.max' => 'type取值：0,1,2',
    );

}