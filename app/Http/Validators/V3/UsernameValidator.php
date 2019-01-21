<?php

namespace App\Http\Validators\V3;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        'username' => [
            'required',
            'not_number',
            'check_sd',
            'is_username',
            'check_emoji',
            'check_len',
            //'unique:sd_user_auth,username',
        ],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'username.required' => '用户名必须存在',
        'username.is_username' => '用户名不能包含特殊字符',
        //'username.unique' => '抱歉，用户名已被占用...',
        'username.check_sd' => '抱歉，用户名已被占用...',
        'username.not_number' => '用户名不能以数字开头',
        'username.check_len' => '用户名过长',
    );

    /*
     * 自定义验证规则或者扩展Validator类
     */

    public function before()
    {
        //用户名不能以数字开头
        $this->extend('not_number', function ($attribute, $value, $parameters) {
            $subStr = substr($value, 0, 1);
            if (is_numeric($subStr)) {
                return false;
            }
            return true;
        });

        // 自定义用户名不能以sd、SD开头
        $this->extend('check_sd', function ($attribute, $value, $parameters) {
            trim($value);
            $subStr = substr($value, 0, 2);
            if (strtolower($subStr) == 'sd') {
                return false;
            }
            return true;
        });

        // 可包含英文字母，汉字，数字，但不支持以数字开头的用户名  用户名不能包含特殊字符
        $this->extend('is_username', function ($attribute, $value, $parameters) {
            if (preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]*$/u', $value)) {
                return true;
            }
            return false;
        });

        // 用户名不能包含表情符号
        $this->extend('check_emoji', function ($attribute, $value, $parameters) {
            if (preg_match('/^[em_(\d+)]*$/u', $value)) {
                return false;
            }
            return true;
        });

        // 若不是1-16个字母或汉字 用户名过长
        $this->extend('check_len', function ($attribute, $value, $parameters) {
            $value = str_replace(" ", "", $value);
            $strLen = Utils::utf8StrLen($value);
            if ($strLen <= 16 && $strLen >= 1) {
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
