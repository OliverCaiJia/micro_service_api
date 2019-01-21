<?php

namespace App\Http\Validators\V4;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 评论内容
 */

class CommentValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'productId' => ['required', 'integer'],
        'platformId' => ['required', 'integer'],
        'resultId' => ['required'],
        'experience' => ['required'],
        'content' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'productId.required' => '产品ID必须存在',
        'productId.integer' => '产品ID必须是整数',
        'platformId.required' => '平台ID必须存在',
        'platformId.integer' => '平台ID必须是整数',
        'resultId.required' => '借款状态要选择哦',
        'experience.required' => '请选择评星',
        'content.required' => '评论将被万千借友看到，请您写一点吧。',
    );

}