<?php
namespace App\Http\Validators\V3;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 列表接口
 */

class ProductdetailValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'productId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'productId.required' => '产品ID必须存在',
        'productId.integer'  => '产品ID必须是整数',
    );

}
