<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 单个网贷产品id验证器
 */

class BillProductIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'billProductId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'billProductId.required' => '请选择网贷产品进行修改',
        'billProductId.integer' => '选择修改网贷产品有误',
    );

}
