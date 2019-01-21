<?php

namespace App\Http\Validators\V2;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

/*
 *
 * 搜索反馈
 */

class SearchfeedbackValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'content' => ['required', 'length_max'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'content.required' => '搜索反馈内容必须存在',
        'content.length_max' => '亲，最多可输入10个汉字哦',
    );

    /*
     * 自定义验证规则或者扩展Validator类
     */

    public function before()
    {
        // 自定义判断长度
        $this->extend('length_max', function ($attribute, $value, $parameters) {
            $len = Utils::utf8StrLen($value);
            if ($len > 20) {
                return false;
            }
            return true;
        });
    }
}
