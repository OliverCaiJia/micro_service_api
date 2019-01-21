<?php

namespace App\Http\Validators\V1;

use App\Http\Validators\AbstractValidator;

/*
 *
 * 地域定位参数验证
 */

class DeviceIdValidator extends AbstractValidator
{

    /**
     * Validation rules
     *
     * @var Array
     * android 15位 部分手机为14位， ios 32位 ， h5 28位
     */
    protected $rules = array(
        'deviceId' => ['required', 'min:14'],
    );

    /**
     * Validation messages
     *
     * @var Array
     */
    protected $messages = array(
        'deviceId.required' => '设备id参数必须存在',
        'deviceId.min' => '您的设备ID格式不正确',
    );

}
