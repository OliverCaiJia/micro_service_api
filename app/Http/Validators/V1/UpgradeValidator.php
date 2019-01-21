<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 * Class UpgradeValidator
 * @package App\Http\Validators\V1\
 * 版本升级参数验证
 */
class UpgradeValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'platType' => ['required'],
        'appType' => ['required'],
        'versionName' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'platType.required' => 'platType必须传值',
        'appType.required' => 'appType必须传值',
        'versionName.required' => 'versionName必须传值',
    );
}