<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 身份证正面信息
 */

class IdcardFrontValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     * 服务器限制2048Kb为2M，因此限制2000Kb可以提前进行提示
     */
    protected $rules = array(
        'cardFront' => ['required', 'image', 'max:2000'],
        'cardPhoto' => ['required', 'image', 'max:2000'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'cardFront.required' => '请上传正面身份证照片',
        'cardFront.image' => '只能上传图片类型的头像',
        'cardFront.max' => '图片最大不能超过2M',
        'cardPhoto.required' => '请上传身份证大头照',
        'cardPhoto.image' => '只能上传图片类型的头像',
        'cardPhoto.max' => '图片最大不能超过2M',
    );

}
