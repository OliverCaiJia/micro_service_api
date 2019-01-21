<?php

namespace App\Http\Validators\V1;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

/*
 *
 * 创建或修改账单验证器
 */

class CreditcardBillValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'billMoney' => ['required', 'numeric', 'min:0'],
        'billTime' => ['required', 'numeric', 'check_bill_time'],
        'billStatus' => ['required', 'integer', 'min:0', 'max:1'],
        'accountId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'billMoney.required' => '还款金额必须存在',
        'billMoney.numeric' => '还款金额不合法',
        'billMoney.min' => '还款金额不合法',
        'billTime.required' => '还款日期必须存在',
        'billTime.numeric' => '还款日期不合法',
        'billTime.check_bill_time' => '还款日期不在范围之内',
        'billStatus.required' => '账单状态必须存在',
        'billStatus.integer' => '账单状态不合法',
        'billStatus.min' => '账单状态不合法',
        'billStatus.max' => '账单状态不合法',
        'accountId.required' => '账户id必须存在',
        'accountId.numeric' => '账户id必须是整数',
    );

    /*
    * 自定义验证规则或者扩展Validator类
    */

    public function before()
    {
        // 自定义不是数字的规则
        $this->extend('check_bill_time', function ($attribute, $value, $parameters) {
            $value = substr($value, 0, 4) . '-' . substr($value, 4, 2);
            $time = time();
            $data = Utils::getLastTime($time, 5, 1);
            if (strtotime($value) >= strtotime($data['before']) && strtotime($value) <= strtotime($data['after'])) {
                return true;
            }
            return false;
        });

    }

}