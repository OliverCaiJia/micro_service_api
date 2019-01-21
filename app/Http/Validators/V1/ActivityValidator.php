<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 资讯接口
 */

class ActivityValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'newsType' => ['required', 'regex:/^\+?[1-9]\d*$/'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'newsType.required' => '类别参数必填',
        'newsType.regex'    => '类别参数必须是整数',
    );

}
