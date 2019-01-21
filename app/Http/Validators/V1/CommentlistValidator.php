<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 评论列表
 */

class CommentlistValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'productId'   => ['required', 'integer'],
        'commentType' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'productId.required'   => '产品ID必须存在',
        'productId.integer'    => '产品ID必须是整数',
        'commentType.required' => '评论类型必须存在',
        'commentType.integer'  => '评论类型必须是整数',
    );

}