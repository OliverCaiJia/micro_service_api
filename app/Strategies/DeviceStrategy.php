<?php

namespace App\Strategies;

use App\Constants\DeviceConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Strategies\AppStrategy;

/**
 * Class SexStrategy
 * @package App\Strategies
 * 地域公共策略
 */
class DeviceStrategy extends AppStrategy
{
    /**
     * @param $citys
     * @return array
     * 地域列表 数据处理
     */
    public static function getCitys($citys, $areaIds)
    {
        //按字母排序
        array_multisort($citys, SORT_ASC);
        $res = [];
        foreach ($citys as $key => $val) {
            if (!in_array($val['name'], $areaIds)) {
                $res[ucfirst($val['domain'])]['citys'][] = $val;
                $res[ucfirst($val['domain'])]['initial'] = ucfirst($val['domain']);
            }
        }

        $res = array_values($res);
        return $res;
    }


    /**
     * @param $userCity
     * @param $id
     * @return int
     * 根据城市名称判定城市id
     */
    public static function getAreaIdByUserCity($userCity, $id)
    {
        if ($userCity == DeviceConstant::CITY_NAME) {
            $areaId = 0;
        } else {
            $areaId = $id;
        }

        return $areaId ? $areaId : 0;
    }

    /**
     * @param $device
     * @return mixed
     * 设备信息数据处理
     */
    public static function getDevicesFromUserId($device)
    {
        //经纬度
        $lonLat = explode(',', $device['lon_lat']);
        //手机客户端唯一标示码
        $data['clientIdentify'] = isset($device['device_id']) ? Utils::removeSpaces($device['device_id']) : '';
        $userAgent = UserAgent::i()->getUserAgent();
        SLogger::getStream()->info('useragent', ['useragent' => $userAgent]);
        //手机型号
        $data['systemModel'] = self::getMobileModel($userAgent);
        //手机系统平台
        $data['systemPhone'] = self::fetchSystemPhone();
        //经度
        $data['lng'] = $lonLat[0];
        //纬度
        $data['lat'] = $lonLat[1];

        return $data;
    }

    /**
     * @param $data
     * @return string
     * 参数随手机设备变化,参数只能为 ios 或 android ,若为其他操作系统,默认传 android
     */
    public static function fetchSystemPhone()
    {
        if (Utils::isiOS()) {
            $system = 'ios';
        } elseif (Utils::isAndroid()) {
            $system = 'android';
        } else {
            $system = 'android';
        }

        return $system;
    }

    /**
     * @param $userAgent
     * @return mixed
     * 获取手机机型
     */
    public static function getMobileModel($userAgent)
    {
        if (Utils::isAndroid() || Utils::isiOS()) {
            preg_match_all("/(?:\()(.*)(?:\))/i", $userAgent, $result);
            $str = explode(')', $result[1][0]);
            $mobileModel = explode(';', $str[0]);
        }
        if (isset($mobileModel[2])) {
            //App
            $res = self::removeSpaces($mobileModel[2]);
        } else {
            $res = '';
        }

        return $res;
    }

    /**
     * @param $param
     * @return mixed|string
     * 移除不需要的符号
     */
    public static function removeSpaces($param)
    {
        return isset($param) ? preg_replace('/[\s|-|(\/)]*/', '', $param) : '';
    }

    /**
     * @param $checkIsPrompt
     * @return mixed
     * 添加定位提示标识
     * 0不提示，1提示
     */
    public static function prompt($data)
    {
        $checkIsPrompt = $data['checkIsPrompt'];
        if ($checkIsPrompt['area_id']) {
            //有登录 有定位 不提示
            $checkIsPrompt['is_prompt'] = 0;
            $checkIsPrompt['is_user'] = 0;
        } else {
            //其他 都提示
            $checkIsPrompt['is_user'] = 0;
            $checkIsPrompt['is_prompt'] = 1;
        }

        return $checkIsPrompt;
    }

}