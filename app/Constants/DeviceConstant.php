<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 地域模块中使用的常量
 */
class DeviceConstant extends AppConstant
{
    //热门城市
    const HOT_CITYS = [
        [
            'id' => 330301,
            'name' => '深圳市',
        ],
        [
            'id' => 330101,
            'name' => '广州市',
        ],
        [
            'id' => 390001,
            'name' => '重庆市',
        ],
        [
            'id' => 400101,
            'name' => '成都市',
        ],
        [
            'id' => 200001,
            'name' => '上海市',
        ],
        [
            'id' => 1,
            'name' => '北京市',
        ],
    ];

    //没有定位出城市  默认城市名称
    const CITY_NAME = '未知';
}

