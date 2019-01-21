<?php

namespace App\Http\Validators\Oneloan;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

class QuickloginValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'mobile'   => ['required', 'is_not_email', 'is_username','is_phone'],
	    'version'  => ['required','integer'],
	    //'sign'     => ['required','alpha_num','size:32']
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'mobile.required' => '登录名必须输入',
        'mobile.is_username' => '登录名格式错误，必须为4-16位字符,可包含中文,英文,数字',
        'mobile.is_not_email' => '请使用手机号或用户名进行登录',
        'mobile.is_phone' => '请输入正确的手机号格式',
	    'version.required' => '版本必须传值',
	    'version.integer' => '版本必须是整数',
	    'code.required' => '验证码必须传值',
	    'code.integer' => '验证码必须是整数',
	    //'code.size' => '验证码必须是4位',
	    'sign.required' => 'sign必须传值',
	    'sign.alpha_num' => 'sign必须是md5后的字符串',
	    'sign.size' => 'sign必须是32位',
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
