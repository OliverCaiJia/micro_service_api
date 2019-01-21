<?php

namespace App\Http\Validators\View;

use App\Http\Validators\AbstractValidator;

/*
 *
 * id必须存在
 */

class IdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'id' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'id.required' => '请选择协议',
        'id.integer' => '请选择协议',
    );

}
