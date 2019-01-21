<?php
namespace App\Http\Validators\Shadow;

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
    );

}
