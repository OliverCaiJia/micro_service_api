<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 分页数据验证
 */

class PageValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'pageSize' => ['regex:/^\+?[1-9]\d*$/'],
        'pageNum'  => ['regex:/^\+?[1-9]\d*$/'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'pageSize.regex'    => '分页大小必须是整数',
        'pageNum.regex'     => '页数必须是整数',
    );

}
