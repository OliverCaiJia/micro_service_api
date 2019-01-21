<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 * Class GeetesCaptchaValidator
 * @package App\Http\Validators\V1
 * 极验 —— 获取图片   极验一次验证
 */
class GeetesCaptchaValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'type'   => ['required'],
        'unique' => ['required', 'size:12'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'type.required'   => '极验请求参数type必须存在',
        'unique.required' => 'unique参数必须存在',
        'unique.size'      => 'unique为12位',
    );
}