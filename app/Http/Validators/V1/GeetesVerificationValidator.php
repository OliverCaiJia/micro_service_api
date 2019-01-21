<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

class GeetesVerificationValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'type'              => ['required'],
        'geetest_challenge' => ['required'],
        'geetest_validate'  => ['required'],
        'geetest_seccode'   => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'type.required'              => '极验请求参数type必须存在',
        'geetest_challenge.required' => '参数geetest_challenge必须存在',
        'geetest_validate.required'  => '参数geetest_validate必须存在',
        'geetest_seccode.required'   => '参数geetest_seccode必须存在',
    );
}