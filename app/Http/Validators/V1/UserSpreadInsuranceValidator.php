<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 */

class UserSpreadInsuranceValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'page'    => 'required', // 页码
        'mobile'  => 'required', //手机号
        'money'   => 'required_if:page, 1', // 借款金额
        'name'    => 'required_if:page, 1',
        'sex'     => 'required_if:page, 1',
        'birthday'=> 'required_if:page, 1', //生日
        'city'    => 'required_if:page, 1', //城市
        'has_creditcard' => 'required_if:page, 1', //是否有信用卡
        'certificate_no' => 'required_if:page, 1', // 身份证号

        // 第二页
        'has_insurance' => 'required_if:page, 2',  // 有无保单
        'house_info'    => 'required_if:page, 2',  // 房产信息
        'car_info'      => 'required_if:page, 2',  // 车产信息
        'occupation'    => 'required_if:page, 2',  // 职业
        'salary_extend' => 'required_if:occupation, 001', // 工资发放方式
        'salary'        => 'required_if:page, 2',  // 工资范围
        'social_security' => 'required_if:page, 2',// 有无社保
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'mobile.required' => '手机号必须填写!',
    );

}
