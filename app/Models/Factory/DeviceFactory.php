<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\PlatformProductArea;
use App\Models\Orm\UserAreas;
use App\Models\Orm\UserDeviceLocation;
use App\Models\Orm\UserDeviceLocationLog;

/**
 * 地域工厂
 * Class SmsFactory
 * @package App\Models\Factory
 */
class DeviceFactory extends AbsModelFactory
{
    /**
     * @param $sortOrder
     * @param $isOpen
     * @return array
     * @sortOrder 1 代表市级
     * @isOpen 1 代表显示
     * @devision 1 显示县级市
     * 分类查询城市名称
     */
    public static function fetchCitys()
    {
        $citys = UserAreas::select(['domain', 'name', 'id'])
            ->where(['is_open' => 1])
            ->where(function ($query) {
                $query->where('sort_order', 1)->orWhere('division', 1);
            })
            ->get()->toArray();

        return $citys ? $citys : [];
    }

    /**
     * @return array
     * 产品城市关联表中的所有产品id
     */
    public static function fetchCityProductIds()
    {
        $cityProductIds = PlatformProductArea::select(['product_id'])
            ->where(['is_delete' => 0])
            ->pluck('product_id')->toArray();

        return $cityProductIds ? $cityProductIds : [];
    }

    /**
     * @param $data
     * @return bool
     * 设备地域日志统计
     */
    public static function createDeviceLocationLog($data)
    {
        $log = new UserDeviceLocationLog();
        $log->device_id = $data['deviceId'];
        $log->user_id = $data['userId'];
        $log->area_id = $data['areaId'];
        $log->user_city = $data['userCity'];
        $log->user_address = $data['userAddress'];
        $log->lon_lat = $data['lonLat'];
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_ip = Utils::ipAddress();
        $log->created_at = date('Y-m-d H:i:s', time());

        return $log->save();
    }

    /**
     * @param $data
     * 修改设备地域定位信息
     */
    public static function updateDeviceLocation($data)
    {
        $location = UserDeviceLocation::updateOrCreate(['device_id' => $data['deviceId']], [
            'device_id' => $data['deviceId'],
            'user_id' => $data['userId'],
            'area_id' => $data['areaId'],
            'user_city' => $data['userCity'],
            'user_address' => $data['userAddress'],
            'lon_lat' => $data['lonLat'],
            'user_agent' => UserAgent::i()->getUserAgent(),
            'updated_ip' => Utils::ipAddress(),
            'updated_at' => date('Y-m-d H:i:s', time()),
        ]);

        return $location->save();
    }

    /**
     * @param $deviceId
     * @return array
     * 地域对应产品id
     */
    public static function fetchProductIdsByDeviceId($deviceId)
    {
        //dd($deviceId);
        $productIds = PlatformProductArea::select(['product_id'])
            ->where(['area_id' => $deviceId])
            ->where(['is_delete' => 0])
            ->pluck('product_id')
            ->toArray();
        return $productIds ? $productIds : [];
    }

    /**
     * @param $userCity
     * @return int
     * @sortOrder 1 代表市级
     * @isOpen 1 代表显示
     * @devision 1 显示县级市
     * 根据城市名称获取城市id
     */
    public static function fetchIdByUserCity($userCity)
    {
        $areaId = UserAreas::select(['id'])
            ->where(['is_open' => 1])
            ->where(function ($query) {
                $query->where('sort_order', 1)->orWhere('division', 1);
            })
            ->where('name', 'like', '%' . $userCity . '%')
            ->first();

        return $areaId ? $areaId->id : 0;
    }

    /**
     * @param $data
     * @return array
     * 根据设备id与用户id查询上次定位城市
     */
    public static function fetchCityByDeviceIdAndUserId($data)
    {
        //$userId = $data['userId'];
        $query = UserDeviceLocation::select(['area_id', 'user_city'])
            ->where(['device_id' => $data['deviceId']])
            ->orderBy('updated_at', 'desc')
            ->limit(1);

//        $query->when($userId, function ($query) use ($userId) {
//            $query->where(['user_id' => $userId]);
//        });

        $city = $query->first();

        return $city ? $city->toArray() : ['area_id' => 0, 'user_city' => '全国'];
    }

    /**
     * @param $deviceId
     * @param $userId
     * @return int
     * 根据设备id与用户id查询上次定位城市id
     */
    public static function fetchCityIdByDeviceIdAndUserId($deviceId)
    {
//        $city = UserDeviceLocation::select(['area_id'])
//            ->where(['device_id' => $deviceId, 'user_id' => $userId])
//            ->first();

        $city = UserDeviceLocation::select(['area_id'])
            ->where(['device_id' => $deviceId])
            ->orderBy('updated_at', 'desc')
            ->limit(1)
            ->first();

        return $city ? $city->area_id : 0;
    }

    /**
     * @param $userId
     * @return array
     * 获取用户最后定位信息
     */
    public static function fetchDevicesByUserId($userId)
    {
        $city = UserDeviceLocation::select()
            ->where(['user_id' => $userId])
            ->orderBy('updated_at', 'desc')
            ->limit(1)
            ->first();

        return $city ? $city : [];
    }

    /**
     * @param $params
     * @return int
     * 根据设备id与用户id查询主键id
     */
    public static function fetchIdByDeviceIdAndUserId($params)
    {
        $id = UserDeviceLocation::select(['id'])
            ->where(['device_id' => $params['deviceId'], 'user_id' => $params['userId']])
            ->first();

        return $id ? $id->id : 0;
    }

}