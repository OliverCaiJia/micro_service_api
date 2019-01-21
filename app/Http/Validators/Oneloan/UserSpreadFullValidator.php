<?php

namespace App\Http\Validators\Oneloan;

use App\Http\Validators\AbstractValidator;

/*
 *  流量推广全部数据验证
 */

class UserSpreadFullValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(

        'name' => ['required', 'is_name'],
        'certificate_no' => ['required', 'is_idcard'], // 身份证号
        'sex' => ['required', 'boolean'],
        'birthday' => ['required', 'regex:/^[12][0-9]{7}$/', 'is_birthday'], //生日
        'city' => ['required'], //城市

        'has_insurance' => ['required', 'integer'],  // 有无保单
        'house_info' => ['required'],  // 房产信息
        'car_info' => ['required'],  // 车产信息
        'occupation' => ['required'],  // 职业

        //001 上班族
        'salary_extend' => ['required_if:occupation,001'], // 工资发放方式
        'accumulation_fund' => ['required_if:occupation,001'],// 公积金
        'work_hours' => ['required_if:occupation,001'],// 工作时间

        //003 私营业主
        'business_licence' => ['required_if:occupation,003'],  // 营业执照

        'salary' => ['required','is_salary'],  // 工资范围
        'social_security' => ['required', 'integer'],// 有无社保
        'has_creditcard' => ['required', 'integer', 'boolean'], //是否有信用卡
        'is_micro' => ['required', 'integer', 'boolean'], //是否有微粒贷
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(

        'name.required' => '姓名必须填写!',
        'name.is_name' => '姓名不符合规则!',

        'mobile.required' => '手机号必须填写!',
        'mobile.regex' => '手机号格式不正确!',
        'has_insurance.required' => '保单信息必须填写!',
        'house_info.required' => '房产信息必须填写!',
        'car_info.required' => '车产信息必须填写!',
        'occupation.required' => '职业信息必须填写!',
        'salary_extend.required_if' => '上班族，公司发放方式必须填写!',
        'accumulation_fund.required_if' => '上班族，公积金必须填写!',
        'work_hours.required_if' => '上班族，工作时间必须填写!',

        'business_licence.required_if' => '私营业主，营业执照必须填写!',

        'salary.required' => '工资范围必须填写!',
        'salary.is_salary' => '请选择月收入',

        'social_security.required' => '社保信息必须填写!',
        'money.required' => '借款金额不能为空',
        'money.numeric' => '金额只能为阿拉伯数字',
        'money.between' => '借款金额不在范围内!',
        'city.required' => '城市必须填写!',
        'city.is_city' => '城市名称格式不正确!',
        'has_creditcard.required' => '信用卡选项必须填写!',
        'has_creditcard.boolean' => '信用卡选项不符合规则!',
        'certificate_no.required' => '身份证号必须填写!',
        'certificate_no.is_idcard' => '身份证号不符合规则!',
        'is_micro.required' => '微粒贷选项必须填写!',
        'is_micro.boolean' => '微粒贷选项不符合规则!',

    );


    public function before()
    {
        //公司范围验证
        $this->extend('is_salary', function ($attribute, $value, $parameters) {

            if(in_array($value,['101','102','103','104','105','106']))
            {
                return true;
            }
            return false;
        });

        //验证年月日的有效性
        $this->extend('is_name', function ($attribute, $value, $parameters) {

            if(mb_strlen($value) >=2 && mb_strlen($value) <=20)
            {
                if (preg_match('/^[\x{4e00}-\x{9fa5}]{2,20}$/u', $value))
                {
                    return true;
                }elseif (preg_match('/^[\x{4e00}-\x{9fa5}]+[·][\x{4e00}-\x{9fa5}]+$/u',$value))
                {
                    return true;
                }
            }


            return false;
        });

        //验证年月日的有效性
        $this->extend('is_birthday', function ($attribute, $value, $parameters) {

            if (preg_match('/^[12][0-9]{7}$/', $value)) {
                $year = mb_substr($value, 0, 4);
                $month = mb_substr($value, 4, 2);
                $day = mb_substr($value, -2);
                if (checkdate(intval($month), intval($day), $year)) {
                    return true;
                }
            }

            return false;
        });


        // 自定义不是数字的规则
        $this->extend('is_idcard', function ($attribute, $value, $parameters) {
            $value = strtoupper(trim($value));
            $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
            $arr_split = array();
            if (!preg_match($regx, $value)) {
                return FALSE;
            }
            if (15 == strlen($value)) { //检查15位
                $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
                @preg_match($regx, $value, $arr_split);
                //检查生日日期是否正确
                $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
                if (!strtotime($dtm_birth)) {
                    return FALSE;
                } else {
                    return TRUE;
                }
            } else {           //检查18位
                $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
                @preg_match($regx, $value, $arr_split);
                $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
                if (!strtotime($dtm_birth)) {  //检查生日日期是否正确
                    return FALSE;
                } else {
                    //检验18位身份证的校验码是否正确。
                    //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                    $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                    $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                    $sign = 0;
                    for ($i = 0; $i < 17; $i++) {
                        $b = (int)$value{$i};
                        $w = $arr_int[$i];
                        $sign += $b * $w;
                    }
                    $n = $sign % 11;
                    $val_num = $arr_ch[$n];
                    if ($val_num != substr($value, 17, 1)) {
                        return FALSE;
                    } else {
                        return TRUE;
                    }
                }
            }
        });
    }

}
