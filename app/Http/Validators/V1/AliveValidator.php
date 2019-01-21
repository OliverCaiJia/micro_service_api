<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 活体认证验证
 */

class AliveValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'imageBest' => ['required', 'image', 'max:2000'],
        'imageEnv' => ['required', 'image', 'max:2000'],
        'delta' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'imageBest.required' => '请上传最佳图片',
        'imageBest.image' => '请上传最佳图片',
        'imageBest.max' => '图片最大不能超过2M',
        'imageEnv.required' => '请上传全景图片',
        'imageEnv.image' => '请上传全景图片',
        'imageEnv.max' => '图片最大不能超过2M',
        'delta.required' => '校验字符串必须存在',
    );

}
