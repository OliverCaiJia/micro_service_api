<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 列表接口
 */

class SpecialValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'fromDate'  => 'date_format:Y-m-d',
        'endDate'   => 'date_format:Y-m-d',
        'pageSize'  => 'integer',
        'pageNum'   => 'integer',
        'specialId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'fromDate.date_format' => '起始日期格式错误',
        'endDate.date_format'  => '结束日期格式错误',
        'pageSize.integer'     => '分页大小必须是整数',
        'pageNum.integer'      => '页数必须是整数',
        'specialId.required'   => '专题参数必须存在',
        'specialId.integer'    => '专题参数必须是整数',
    );

}
