<?php
namespace App\Http\Validators\V2;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 列表接口
 */

class CreditListValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'status' => ['required'],
        'pageSize' => ['required']
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'status.required' => '积分类型必须存在',
        'pageSize.required' => '分页页码必须存在'
    );

}
