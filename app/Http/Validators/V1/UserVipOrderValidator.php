<?php

namespace App\Http\Validators\V1;
use App\Http\Validators\AbstractValidator;

class UserVipOrderValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     * android 15位 ， ios 32位 ， h5 28位
     */
    protected $rules = array(
        'payType' => ['required','integer', 'min:1', 'max:3'],
        'terminalId' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'payType.required' => 'payType必须存在',
        'payType.integer' => 'payType必须为整型',
        'payType.min' => 'payType取值：1,2,3',
        'payType.max' => 'payType取值：1,2,3',
        'terminalId.required' => '设备号必须存在',
    );
}