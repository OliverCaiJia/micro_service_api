<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserLocation;

/**
 * 地理位置处理工厂类
 */
class LocationFactory extends AbsModelFactory
{
    /**
     * @param array $data
     * @param $userId
     * @return bool
     * 定位 —— 统计用户地址
     */
    public static function createLocation($data = [], $userId)
    {
        $loca                = new UserLocation();
        $loca->user_id       = $userId;
        $loca->location_name = trim($data['address']);
        $loca->location_type = trim($data['addressType']);
        $loca->ctime         = date('Y-m-d H:i:s', time());
        $loca->create_ip = Utils::ipAddress();
        return $loca->save();
    }


}
