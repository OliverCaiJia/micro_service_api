<?php

namespace App\Http\Validators\V1;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

/*
 *
 * 添加或修改网贷平台验证器
 */

class BillProductValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'billProductId' => ['integer'],
        'productId' => ['integer'],
        'productName' => ['required', 'check_product_name'],
        'productPeriodTotal' => ['required', 'integer', 'between:1,48'],
        'productRepayDay' => ['required', 'check_date'],
        'productBillPeriodNum' => ['required', 'integer', 'between:1,48'],
        'billMoney' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        'alertStatus' => ['integer', 'min:0', 'max:1'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'billProductId.integer' => '请选择网贷平台进行修改',
        'productId.integer' => '请选择网贷平台名称',

        'productName.required' => '平台名称必填',
        'productName.check_product_name' => '平台名称格式错误',

        'productPeriodTotal.required' => '总期数必填',
        'productPeriodTotal.integer' => '总期数格式错误',
        'productPeriodTotal.between' => '总期数不在范围内',

        'productRepayDay.required' => '还款日必填',
        'productRepayDay.check_date' => '还款日不在范围内',

        'productBillPeriodNum.required' => '当前期数必填',
        'productBillPeriodNum.integer' => '当前期数格式不正确',
        'productBillPeriodNum.between' => '当前期数不在范围内',

        'billMoney.required' => '每期应还金额必填',
        'billMoney.max' => '金额不在范围内',
        'billMoney.min' => '金额不在范围内',

        'alertStatus.integer' => '还款提醒标识有误1',
        'alertStatus.min' => '还款提醒标识有误2',
        'alertStatus.max' => '还款提醒标识有误',
    );

    /*
    * 自定义验证规则或者扩展Validator类
    */

    public function before()
    {
        // 自定义规则检查网贷产品名称
        $this->extend('check_product_name', function ($attribute, $value, $parameters) {
            if (preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u", $value)) {
                $strLen = Utils::utf8StrLen($value);
                if ($strLen <= 16 && $strLen >= 3) {
                    return true;
                }
            }
            return false;
        });

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
