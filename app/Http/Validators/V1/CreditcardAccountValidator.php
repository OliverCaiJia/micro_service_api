<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 新增还款提醒
 */

class CreditcardAccountValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'bankUsageId' => ['required', 'integer'],
        'creditcardNum' => ['required', 'numeric','digits:4'],
        'repayDay' => ['required', 'min:1', 'max:28'],
        'billDate' => ['numeric', 'min:1', 'max:28'],
        'repayAmount' => ['numeric', 'min:0'],
        //'registrationId' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'bankUsageId.required' => '信用卡所属银行必选',
        'bankUsageId.integer' => '信用卡所属银行必选',
        'creditcardNum.required' => '信用卡号必填',
        'creditcardNum.numeric' => '信用卡号不合法',
        'creditcardNum.digits' => '请填写正确银行卡后四位',
        'repayDay.required' => '还款日必选',
        'repayDay.numeric' => '还款日不合法',
        'billDate.numeric' => '账单日不合法',
        'repayAmount.numeric' => '额度不合法',
        'repayAmount.min' => '额度不合法',
        //'registrationId.required' => '推送id参数必须存在',
    );

}