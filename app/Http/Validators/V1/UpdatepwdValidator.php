<?php

namespace App\Http\Validators\V1;

use App\Helpers\Utils;
use App\Http\Validators\AbstractValidator;

class UpdatepwdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
	    'password'     => ['required','alpha_num','size:32']
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
	    'password.required' => 'password必须传值',
	    'password.alpha_num' => 'password必须是md5后的字符串',
	    'password.size' => 'password必须是32位',
    );
	

}
