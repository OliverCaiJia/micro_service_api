<?php

namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\UserAgent;
use App\Http\Controllers\Controller;
use App\Models\Factory\DataFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

/**
 * Class DataController
 * @package App\Http\Controllers\V1
 * 数据统计
 */
class DataController extends Controller
{
    /**
     * post机申请记录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPosLog(Request $request)
    {
        $params = $request->all();
        $result = DataFactory::insertPostLog($params);

        if (!$result) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2101), 2101);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * 统计活跃用户
     */
    public function updateActiveUser(Request $request)
    {
        $userId = $request->user()->sd_user_id;

        //修改用户的访问时间visit_time
        $visitTime = UserFactory::updateActiveUser($userId);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * 产品申请点击流水统计
     */
    public function createProductApplyLog(Request $request)
    {
        $productId = $request->input('productId');
        $userId = $request->user()->sd_user_id;
        //获取用户信息
        $userArr = UserFactory::fetchUserNameAndMobile($userId);
        //获取产品信息
        $productArr = ProductFactory::fetchProductname($productId);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($userId);
        //获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);

        if (empty($userArr) || empty($productArr) || empty($deliveryArr) || empty($deliveryId)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //产品申请点击流水统计
        DeliveryFactory::createProductApplyLog($userId, $userArr, $productArr, $deliveryArr);
        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return mixed
     * 宫格产品申请点击流水统计
     */
    public function createProductApplyGonggeLog(Request $request)
    {
        $productId = $request->input('productId');
        //获取产品信息
        $productArr = ProductFactory::fetchProductname($productId);
        if (empty($productArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //获取平台url
        $platformUrl = PlatformFactory::fetchPlatformUrl($productArr['platform_id']);

        $productArr['platform_url'] = $platformUrl;
        $productArr['user_agent'] = UserAgent::i()->getUserAgent();
        //宫格产品申请点击流水统计
        DeliveryFactory::createProductApplyGonggeLog($productArr);
        //单个平台点击立即申请数据统计
        PlatformFactory::updatePlatformClick($productArr['platform_id']);
        //单个产品点击立即申请数据统计
        ProductFactory::updateProductClick($productId);

        return RestResponseFactory::ok(RestUtils::getStdObj());

    }

    /**
     * 投放统计
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUserIdfa(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['idfaId'] = $request->input('idfaId', '');

        //根据投放标识、用户id为0查数据
        $idfaByIds = DataFactory::fetchUserIdfaByUserIdEmpty($data);

        if ($idfaByIds) {
            $res = DataFactory::createUserIdfa($data);
        } else {
            $res = DataFactory::updateUserIdfaByIds($data);
        }

        if (empty($res)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }
}