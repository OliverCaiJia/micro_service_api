<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 精确匹配参数验证
 */

class ExactValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'loanMoney' => ['required', 'string'],
        'loanTimes' => ['required', 'string'],
        'useType' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'loanMoney.required' => '借款金额参数必须存在',
        'loanTimes.integer'  => '借款期限参数必须是整数',
        'useType.required'   => '借款类型参数必须存在',
    );

}
