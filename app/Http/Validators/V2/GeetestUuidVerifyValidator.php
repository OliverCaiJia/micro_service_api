<?php
namespace App\Http\Validators\V2;

use App\Http\Validators\AbstractValidator;

/**
 * Class GeetesCaptchaValidator
 * @package App\Http\Validators\V1
 * 极验 —— 获取图片   极验一次验证
 */
class GeetestUuidVerifyValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'client_type'   => ['required','in:h5,native,web'],
        'uuid' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'client_type.required'   => '参数client_type必须存在',
        'client_type.in'         => '参数client_type必须是h5,native,web其中一个',
        'uuid.required'          => '参数uuid必须存在',
    );
}