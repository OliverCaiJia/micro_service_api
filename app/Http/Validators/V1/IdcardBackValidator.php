<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 身份证反面信息
 */

class IdcardBackValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     * 服务器限制2048Kb为2M，因此限制2000Kb可以提前进行提示
     */
    protected $rules = array(
        'cardBack' => ['required', 'image', 'max:2000'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'cardBack.required' => '请上传正面身份证照片',
        'cardBack.image' => '只能上传图片类型的头像',
        'cardBack.max' => '图片最大不能超过2M',
    );

}
