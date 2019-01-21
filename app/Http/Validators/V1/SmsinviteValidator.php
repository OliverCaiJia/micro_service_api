<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 短信邀请验证
 */

class SmsinviteValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'mobile' => ['required','is_phone'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'mobile.required' => '手机号必须存在',
    );

    public function before()
    {
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
