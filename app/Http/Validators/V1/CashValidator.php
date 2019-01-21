<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 验证接口
 */

class CashValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    // 要验证的字段
    protected $rules = array(
        'realName' => ['required', 'regex:/^[\x{4e00}-\x{9fa5}\·]{2,}$/u'],
        'IDCard' => 'required|is_idcard|is_eighteen'
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    //自定义错误信息
    protected $messages = array(
        'realName.required' => '真实姓名必填',
        'realName.regex' => '真实姓名格式不正确',
        'IDCard.required' => '身份证号必填',
        'IDCard.is_idcard' => '请输入正确的身份证号码', // '身份证号不符合要求',
        'IDCard.is_eighteen' => '理财人须年满18周岁'
    );
    
    public $codes = array(
        'realName.required' => 111,
    );

    /**
     * 自定义验证规则或者扩展Validator类
     */
    public function before()
    {
        // 自定义不是数字的规则
        $this->extend('is_idcard', function($attribute, $value, $parameters) {
            $value = strtoupper(trim($value));
            $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
            $arr_split = array();
            if (!preg_match($regx, $value))
            {
                return FALSE;
            }
            if (15 == strlen($value))
            { //检查15位
                $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
                @preg_match($regx, $value, $arr_split);
                //检查生日日期是否正确
                $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
                if (!strtotime($dtm_birth))
                {
                    return FALSE;
                }
                else
                {
                    return TRUE;
                }
            }
            else
            {           //检查18位
                $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
                @preg_match($regx, $value, $arr_split);
                $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
                if (!strtotime($dtm_birth))
                {  //检查生日日期是否正确
                    return FALSE;
                }
                else
                {
                    //检验18位身份证的校验码是否正确。
                    //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                    $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                    $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                    $sign = 0;
                    for ($i = 0; $i < 17; $i++) {
                        $b = (int) $value{$i};
                        $w = $arr_int[$i];
                        $sign += $b * $w;
                    }
                    $n = $sign % 11;
                    $val_num = $arr_ch[$n];
                    if ($val_num != substr($value, 17, 1))
                    {
                        return FALSE;
                    }
                    else
                    {
                        return TRUE;
                    }
                }
            }
            return false;
        });

        $this->extend('is_eighteen', function($attribute, $value, $parameters) {
            $id = strtoupper(trim($value));
            $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
            if (!preg_match($regx, $id))
            {
                return false;
            }

            $ages = [];
            if (15 == strlen($id))
            { //检查15位
                $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
                preg_match($regx, $id, $ages);
                $ages[2] = '19' . $ages[2];
            }
            else
            {
                $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
                preg_match($regx, $id, $ages);
            }

            // My age
            $myYears = $ages[2];
            $myMounth = $ages[3];
            $myDay = $ages[4];
            // Today
            $nowY = intval(date('Y'));
            $nowM = intval(date('m'));
            $nowD = intval(date('d'));

            //大于18岁
            $age = $nowY - $myYears;
            if ($age == 18)
            {
                //等于十八岁出现情况
                $ageM = $nowM - $myMounth;
                if ($ageM != 0)
                {
                    return ($ageM > 0);
                }
                return $nowD - $myDay >= 0;
            }
            return ($age - 18) > 0;
        });
    }

}
