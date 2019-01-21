<?php
namespace App\Http\Validators\V2;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 评论内容
 */

class ReplyValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'commentId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'commentId.required' => '评论ID必须存在',
        'commentId.integer' => '评论ID必须是整数',
    );

}