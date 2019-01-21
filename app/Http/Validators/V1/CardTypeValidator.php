<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 * Class CardTypeValidator
 * @package App\Http\Validators\V1
 * 卡片类型验证
 */
class CardTypeValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'cardType' => ['required','integer','min:1','max:2'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'cardType.required' => '银行卡类型参数必须存在',
        'cardType.integer' => '银行卡类型参数不正确',
        'cardType.min' => '银行卡类型参数不正确',
        'cardType.max' => '银行卡类型参数不正确',
    );

}