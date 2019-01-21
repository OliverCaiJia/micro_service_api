<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 不想看产品标签
 */

class ProductBlackTagValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'productId' => ['required', 'integer'],
        'type' => ['required', 'integer', 'between:1,2'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'productId.required' => '产品ID必须存在',
        'productId.integer' => '产品ID必须是整数',
        'type.required' => '标签类型必须存在',
        'type.integer' => '标签类型必须是整数',
        'type.between' => '标签类型数值不匹配',
    );

}
