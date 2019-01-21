<?php

namespace App\Http\Validators\V2;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/*
 *
 * 对Data里面进行验证
 */

class BairongValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'mobile' => [
            'required',
            'check_legal',
        ],
        'user_type' => ['required','check_size'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'mobile.required' => '手机号必须存在',
        'mobile.check_legal' => '手机号不合法',
        'user_type.required' => '用户(新老)必须存在',
        'user_type.check_size' => '数据不正确'
    );

    /**
     * Validation codes
     *
     * @var Array
     */
    public $codes = array(
        'mobile.required' => '1001',
        'mobile.check_legal' => '1002',
    );

    /*
     * 自定义验证规则或者扩展Validator类
     */

    public function before()
    {
        //自定义规则检查用户手机号是否合法
        $this->extend('check_legal',function($attribute,$value,$paramters){
            if (preg_match('/^1[3|4|5|6|7|8|9]\d{9}$/', $value)) {
                return true;
            } else {
                return false;
            }
        });

        //参数值
        $this->extend('check_size',function ($attribute,$value,$paramters){
            if($value < 2)
            {
                return true;
            } else {
                return false;
            }
        });

    }

    /*
     * sometimes添加条件验证规则
     */

    public function after()
    {

    }

}
