<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 * 投放设备标识验证
 * Class IdfaIdValidator
 * @package App\Http\Validators\V1
 */
class IdfaIdValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'idfaId' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'idfaId.required' => '投放设备必须存在',
    );

}