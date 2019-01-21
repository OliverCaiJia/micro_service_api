<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 修改face返回的用户信息
 */

class FaceUserinfoValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */

    protected $rules = array(
        'realname' => 'required|regex:/^[\x{4e00}-\x{9fa5}\·]{2,}$/u',
        'sex' => 'required|integer|min:0|max:1',
        'certificateNo' => 'required|is_idcard',
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'realname.required' => '真实姓名必填',
        'realname.regex' => '真实姓名格式不正确',
        'sex.required' => '性别必填',
        'sex.integer' => '性别格式不正确',
        'sex.min' => '性别格式不正确',
        'sex.max' => '性别格式不正确',
        'certificateNo.required' => '身份证号必填',
        'certificateNo.is_idcard' => '请输入正确的身份证号码', // '身份证号不符合要求',
    );

    public function before()
    {
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
            return false;
        });
    }

}
