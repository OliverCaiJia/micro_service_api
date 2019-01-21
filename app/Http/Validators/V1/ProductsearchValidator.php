<?php

namespace App\Http\Validators\V1;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

/*
 *
 * 搜索接口
 */

class ProductsearchValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'product_name' => ['required', 'length_max'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'product_name.required' => '搜索内容必须存在',
        'product_name.length_max' => '亲，最多可输入10个汉字哦',
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
