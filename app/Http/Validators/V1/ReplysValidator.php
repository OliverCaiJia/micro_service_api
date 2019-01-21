<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 回复列表验证类
 */

class ReplysValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'commentId' => ['required','integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'commentId.required' => '回复评论id参数commentId必须存在',
        'commentId.integer' => '回复评论id参数commentId必须是整数',
    );

}
