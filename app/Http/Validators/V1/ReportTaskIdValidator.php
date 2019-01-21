<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 报告任务id验证器
 */

class ReportTaskIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'reportTaskId' => ['required','integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'reportTaskId.required' => '报告id必须存在',
        'reportTaskId.integer' => '报告id必须是整数',
    );

}
