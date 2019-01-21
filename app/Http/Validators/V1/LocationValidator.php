<?php
namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/**
 * Class LocationValidator
 * @package App\Http\Validators\V1
 * 地理位置验证类
 */
class LocationValidator extends AbstractValidator
{
    /**
     * Validation rules
     *
     * @var Array
     */
    protected $rules = array(
        'address'     => ['required'],
        'addressType' => ['required'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'address.required'     => '地址参数locationName必须存在',
        'addressType.required' => '地址类型参数addressType必须存在',
    );

}