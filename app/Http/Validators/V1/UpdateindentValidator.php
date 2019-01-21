<?php

namespace App\Http\Validators\V1;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

class UpdateindentValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'indent'   => ['required','integer','min:1'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'indent.required'  => 'indent必须传值',
        'indent.integer'  => 'indent必须是整数',
        'indent.min'  => 'indent最小值为1',
    );


}
