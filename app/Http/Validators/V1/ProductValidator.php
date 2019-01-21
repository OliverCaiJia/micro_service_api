<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 列表接口
 */

class ProductValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'fromDate'    => 'date_format:Y-m-d',
        'endDate'     => 'date_format:Y-m-d',
        'pageSize'    => 'integer',
        'pageNum'     => 'integer',
        'productType' => ['required', 'integer'],
        'useType'     => ['required', 'integer'],
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
        'productType.required' => '产品利率参数必须存在',
        'productType.integer'  => '产品利率参数必须是整数',
        'useType.required'     => '产品参数必须存在',
        'useType.integer'      => '产品参数必须是整数',
    );

}
