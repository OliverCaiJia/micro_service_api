<?php

namespace App\Http\Validators\V2;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 用户头像验证器
 */

class PhotoValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     * 服务器限制2048Kb为2M，因此限制2000Kb可以提前进行提示
     */
    protected $rules = array(
        'userPhoto' => ['image', 'max:2000', 'required_without:file'],
        'file' => ['image', 'max:2000', 'required_without:userPhoto'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'userPhoto.required' => '请上传头像',
        'userPhoto.image' => '只能上传图片类型的头像',
        'userPhoto.max' => '图片最大不能超过2M',

        'file.required' => '请上传头像',
        'file.image' => '只能上传图片类型的头像',
        'file.max' => '图片最大不能超过2M',
    );

}
