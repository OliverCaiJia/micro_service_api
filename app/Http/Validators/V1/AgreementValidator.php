<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 协议验证
 */

class AgreementValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'agreement' => ['required', 'integer', 'size:1'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'agreement.required' => '请勾选协议！',
        'agreement.integer' => '请勾选协议！',
        'agreement.size' => '请勾选协议！',
    );

}
