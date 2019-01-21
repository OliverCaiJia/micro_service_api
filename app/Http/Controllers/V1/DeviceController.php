<?php

namespace App\Http\Controllers\V1;

use App\Constants\DeviceConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\DeviceFactory;
use App\Strategies\DeviceStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Class ReplyController
 * @package App\Http\Controllers\V1
 * 地域
 */
class DeviceController extends Controller
{

    /**
     * @return mixed
     * 地域列表
     */
    public function fetchDevices()
    {
        //先从cache中读取
        if (Cache::get('device')) {
            $resCitys = Cache::get('device');
            return RestResponseFactory::ok($resCitys);
        }
        //分类查询城市名称
        $citys = DeviceFactory::fetchCitys();
        //市辖区等显示id
        $areaIds = ['市辖区', '县', '省直辖县级行政区划', '自治区直辖县级行政区划'];
        //数据处理
        $resCitys = DeviceStrategy::getCitys($citys, $areaIds);
        //热门城市
        $cityData['hotCity'] = DeviceConstant::HOT_CITYS;
        $cityData['list'] = $resCitys;

        //存cache
        Cache::put('device', $cityData, 86400);

        return RestResponseFactory::ok($cityData);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 设备地域 统计
     */
    public function updateDeviceLocation(Request $request)
    {
        $data = $request->all();
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        //1需要修改设备地域  2只记录设备地域流水
        $locationType = $request->input('locationType', 1);

        //城市id选定值
        if (empty($data['areaId'])) {
            //城市id
            $id = DeviceFactory::fetchIdByUserCity($data['userCity']);
            $data['areaId'] = DeviceStrategy::getAreaIdByUserCity($data['userCity'], $id);
        }

        if ($data['userCity'] != DeviceConstant::CITY_NAME) {
            //设备地域日志统计
            DeviceFactory::createDeviceLocationLog($data);
            //修改设备地域
            DeviceFactory::updateDeviceLocation($data);
        }

        return RestResponseFactory::ok(['areaId' => $data['areaId']]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 获取用户上次定位信息
     */
    public function fetchCity(Request $request)
    {
        //设备id
        $data['deviceId'] = $request->input('deviceId', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        //根据设备id与用户id查询上次定位城市
        $city = DeviceFactory::fetchCityByDeviceIdAndUserId($data);

        return RestResponseFactory::ok($city);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 信用卡判断定位提示是否弹出
     */
    public function checkIsPrompt(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //设备id
        $data['deviceId'] = $request->input('deviceId');
        //判断是否需要进行定位提示
        $data['checkIsPrompt'] = DeviceFactory::fetchCityByDeviceIdAndUserId($data);
        //数据处理，加入提示字段
        $prompt = DeviceStrategy::prompt($data);

        return RestResponseFactory::ok($prompt);
    }

}
