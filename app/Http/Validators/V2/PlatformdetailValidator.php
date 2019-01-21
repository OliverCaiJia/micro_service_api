<?php
namespace App\Http\Validators\V2;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 列表接口
 */

class PlatformdetailValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'platformId' => ['required', 'integer'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'platformId.required' => '平台ID必须存在',
        'platformId.integer'  => '平台ID必须是整数',
    );

}
