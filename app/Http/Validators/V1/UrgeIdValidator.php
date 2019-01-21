<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 * Class UpgradeValidator
 * @package App\Http\Validators\V1\
 * 催审id验证器
 */
class UrgeIdValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'urgeId' => ['required','integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'urgeId.required' => '催审id必须存在',
        'urgeId.integer' => '催审id必须是整形',
    );
}