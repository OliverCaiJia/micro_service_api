<?php

namespace App\Http\Validators\Oneloan;

use App\Http\Validators\AbstractValidator;

/*
 *  流量推广基础数据验证
 */

class UserSpreadBasicValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(

        'money' => ['required', 'numeric', 'min:100', 'max:10000000'],
        'name' => ['is_name'],
        'certificate_no' => ['is_idcard'], // 身份证号
        'sex' => ['boolean'],
        'birthday' => ['regex:/^[12][0-9]{7}$/', 'is_birthday'], //生日
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'money.required' => '贷款金额必须填写!',
        'money.numeric' => '贷款金额只能是数字!',
        'money.min' => '金额最小为100!',
        'money.max' => '金额最大为100万!',
        'name.required' => '姓名必须填写!',
        'name.is_name' => '姓名不符合规则!',
        'sex.required' => '性别必须填写!',
        'sex.boolean' => '性别不符合规则!',
        'birthday.required' => '生日必须填写!',
        'birthday.regex' => '生日格式不正确!',
        'birthday.is_birthday' => '生日格式不具有效性',
        'certificate_no.required' => '身份证号必须存在',
        'certificate_no.is_idcard' => '身份证号格式不正确',
        'city.required' => '现居住城市必须存在',
    );


    public function before()
    {
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
