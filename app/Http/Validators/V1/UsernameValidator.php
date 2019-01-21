<?php

namespace App\Http\Validators\V1;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

/*
 *
 * 用户名验证
 */

class UsernameValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'userName' => ['is_username','unique:sd_user_auth,username']
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'userName.is_username' => '1-20个字符，支持中英文、数字和特殊符号',
        'userName.not_numeric' => '用户名不能为纯数字',
        'userName.unique' => '抱歉，用户名已被占用...'
    );

    /*
     * 自定义验证规则或者扩展Validator类
     */

    public function before()
    {
        // 自定义不是数字的规则
        $this->extend('not_numeric', function($attribute, $value, $parameters) {
            if (is_numeric($value))
            {
                return false;
            }
            return true;
        });

        // 自定义规则检查用户名
        $this->extend('is_username', function($attribute, $value, $parameters) {
            $value = str_replace(" ","",$value);
            $strLen = Utils::utf8StrLen($value);
            if ($strLen <= 20 && $strLen >= 1)
            {
                return true;
            }
            return false;
        });
    }

    /*
     * sometimes添加条件验证规则
     */

    public function after()
    {
        
    }

}
