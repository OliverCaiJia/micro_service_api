<?php
namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\LocationFactory;
use Illuminate\Http\Request;

/**
 * Class LocationController
 * @package App\Http\Controllers\V1
 * 地理位置
 */
class LocationController extends Controller
{
    /**
     * @param Request $request
     * 定位 —— 统计用户地址
     */
    public function createLocation(Request $request)
    {
        $data   = $request->all();
        $userId = $request->user()->sd_user_id;
        //添加自动定位
        $location = LocationFactory::createLocation($data,$userId);
        if(empty($location)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(),RestUtils::getErrorMessage(2105),2105);
        }
        return RestResponseFactory::ok($location);
    }
}