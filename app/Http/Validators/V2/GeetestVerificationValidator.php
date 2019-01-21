<?php
namespace App\Http\Validators\V2;

use App\Http\Validators\AbstractValidator;

class GeetestVerificationValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'client_type'       => ['required', 'in:h5,native,web'],
        'uuid'              => ['required'],
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
        'client_type.required'       => '参数client_type必须存在',
        'client_type.in'             => '参数client_type必须是h5,native,web其中一个',
        'uuid.required'              => '参数uuid必须存在',
        'geetest_challenge.required' => '参数geetest_challenge必须存在',
        'geetest_validate.required'  => '参数geetest_validate必须存在',
        'geetest_seccode.required'   => '参数geetest_seccode必须存在',
    );
}