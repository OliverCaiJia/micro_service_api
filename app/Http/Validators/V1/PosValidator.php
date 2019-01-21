<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 * post机
 */

class PosValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'name'    => ['required'],
        'mobile'     => ['required', 'check_legal'],
        'address'    => ['required'],
        'content'     => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'name.required' => '姓名必须存在',
        'address.required' => '地址必须存在',
        'content.required' => '内容必须存在',
        'mobile.required' => '手机号必须存在',
        'mobile.check_legal' => '手机号不合法',
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

    }

    /*
    * sometimes添加条件验证规则
    */

    public function after()
    {

    }
}
