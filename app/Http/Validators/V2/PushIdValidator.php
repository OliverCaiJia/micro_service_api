<?php

namespace App\Http\Validators\V2;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 任务弹窗id
 */

class PushIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'pushId' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'pushId.required' => '主键参数必须存在',
    );

}
