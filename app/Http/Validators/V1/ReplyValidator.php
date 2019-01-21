<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 创建回复验证类
 */

class ReplyValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'commentId' => ['required','integer'],
        'content' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'commentId.required' => '回复评论id参数commentId必须存在',
        'commentId.integer' => '回复评论id参数commentId必须是整数',
        'content.required' => '回复评论内容必须存在',
    );

}
