<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 列表接口
 */

class SearchValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'pageSize'    => 'integer',
        'pageNum'     => 'integer',
        'productType' => 'integer',
        'loanMoney'   => 'integer',
        'indent'      => 'integer',
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'pageSize.integer'    => '分页大小必须是整数',
        'pageNum.integer'     => '页数必须是整数',
        'productType.integer' => '搜索利率参数必须是整数',
        'loanMoney.integer'   => '借款金额必须是整数',
        'indent.integer'      => '身份参数必须是整数',
    );

}
