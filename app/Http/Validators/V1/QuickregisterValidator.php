<?php

namespace App\Http\Validators\V1;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

class QuickregisterValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'mobile' => ['required', 'is_not_email', 'is_username','is_phone'],
        'password' => 'required|alpha_num|size:32'
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'keywords.required' => '登录名必须输入',
        'keywords.is_username' => '登录名格式错误，必须为4-16位字符,可包含中文,英文,数字',
        'keywords.is_not_email' => '请使用手机号或用户名进行登录',
        'keywords.is_phone' => '请输入正确的手机号格式',
        'password.required' => '密码必须输入',
        'password.alpha_num' => '密码格式错误',
        'password.size' => '密码必须是MD5字符串',
    );

    /**
     * Validation codes
     *
     * @var Array
     */
    public $codes = array(
        'keywords.required' => '1001',
        'keywords.is_username' => '1002',
        'keywords.is_not_email' => '1003',
        'password.required' => '1004',
        'password.alpha_num' => '1005',
        'password.size' => '1006',
    );

    /*
     * 自定义验证规则或者扩展Validator类
     */

    public function before()
    {
        // 自定义规则检查用户名
        $this->extend('is_username', function($attribute, $value, $parameters)
        {
            if (preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_]+$/u", $value))
            {
                $strLen = Utils::utf8StrLen($value);
                if ($strLen <= 16 && $strLen >= 4)
                {
                    return true;
                }
            }
            return false;
        });

        // 移动端不支持邮箱登陆
        $this->extend('is_not_email', function($attribute, $value, $parameters)
        {
            return !filter_var($value, FILTER_VALIDATE_EMAIL);
        });
	    
	    //自定义规则检查用户手机号
	    $this->extend('is_phone',function($attribute,$value,$paramters){
	    	if (preg_match('/^1[3|4|5|6|7|8|9]\d{9}$/', $value)) {
	    		return true;
		    } else {
		    	return false;
		    }
	    });
    }

}
