<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 获取单条帮助验证器
 */

class HelpTypeIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'typeId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'typeId.required' => '帮助分类id必须存在',
        'typeId.integer' => '帮助分类id必须是整数',
    );

}
