<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 添加或修改信用卡平台验证器
 */

class BillCreditcardValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'billBankId' => ['required', 'integer'],
        'creditcardNum' => ['required', 'regex:/^\d{4}$/'],
        'billDate' => ['required', 'check_date', 'regex:/^\d{1,2}$/'],
        'repayDate' => ['required', 'check_date', 'regex:/^\d{1,2}$/'],
        'quota' => ['min:0', 'max:999999.99'],
        'alertStatus' => ['integer', 'min:0', 'max:1'],
        'creditcardId' => ['integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'billBankId.required' => '请选择银行',
        'billBankId.integer' => '请选择银行',
        'creditcardNum.required' => '请输入卡号末四位',
        'creditcardNum.regex' => '卡号末四位只能为数字',
        'billDate.required' => '请输入账单日期',
        'billDate.check_date' => '账单日期不在当月范围内',
        'billDate.regex' => '账单日期不在单月范围内!',
        'repayDate.required' => '请输入还款日期',
        'repayDate.check_date' => '还款日期不在当月范围内',
        'repayDate.regex' => '还款日期不在当月范围内',
        'quota.max' => '额度最大值为999,999.99',
        'quota.min' => '额度不能为负数',
        'alertStatus.integer' => '还款提醒标识有误1',
        'alertStatus.min' => '还款提醒标识有误2',
        'alertStatus.max' => '还款提醒标识有误',
        'creditcardId.integer' => '选择修改信用卡有误',
    );


    public function before()
    {
        // 自定义账单日与还款日的范围
        $this->extend('check_date', function ($attribute, $value, $parameters) {
            $value = intval($value);
            if ($value >= 1 && $value <= 28) {
                return true;
            }
            return false;
        });

    }
}
