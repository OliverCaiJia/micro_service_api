<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 帮助中心 —— 提问&反馈
 */

class FeedbackValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'feedback' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'feedback.required'  => '内容不能为空',
    );

    public function before()
    {
        // 字符串不能为空
        $this->extend('not_empty', function ($attribute, $value, $parameters) {
            if (empty($value)) {
                return false;
            }
            return true;
        });

    }

}
