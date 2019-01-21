<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 列表接口
 */

class OauthValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'productId'  => ['required', 'integer'],
        'platformId' => ['required', 'integer'],
        'type'       => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'productId.required'  => '产品ID必须存在',
        'productId.integer'   => '产品ID必须是整数',
        'platformId.required' => '平台ID必须存在',
        'platformId.integer'  => '平台ID必须是整数',
        'type.required'       => '点击借款参数必须存在',
        'type.integer'        => '点击借款参数必须是整数',
    );

}
