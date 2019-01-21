<?php

namespace App\Http\Controllers\V2;

use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\BankFactory;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\ExactFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserinfoFactory;
use App\Models\Factory\UserVipFactory;
use App\Models\Orm\UserVip;
use App\Strategies\BankStrategy;
use App\Strategies\ExactStrategy;
use App\Strategies\UserinfoStrategy;
use Illuminate\Http\Request;

/**
 * Class ExactsController
 * @package App\Http\Controllers\V1
 * 精准匹配
 */
class ExactController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 精确匹配数据
     */
    public function fetchExactMatchDatas(Request $request)
    {
        //接收用户需要匹配的值
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        //地域id
        $areaId = $request->input('areaId', '');
        $deviceId = $request->input('deviceId', '');
        $data['userId'] = $userId;
        $data['mobile'] = $request->user()->mobile;
        //根据设备id与用户id获取城市id
        $cityId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($deviceId);
        $data['cityId'] = !empty($cityId) ? $cityId : $areaId;
        //所有产品id
        $data['productIds'] = ProductFactory::fetchProductIds();
        //产品城市关联表中的所有产品id
        $data['cityProductIds'] = DeviceFactory::fetchCityProductIds();
        //地域对应产品id
        $data['deviceProductIds'] = DeviceFactory::fetchProductIdsByDeviceId($data['cityId']);

        //是否是会员
        $data['userVipType'] = UserVipFactory::fetchUserVipToTypeByUserId($data['userId']);
        if ($data['userVipType']) {
            //会员
            $data['productVipIds'] = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $data['productVipIds'] = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }

        //身份
        $data['indent'] = UserFactory::fetchUserIndent($userId);
        //修改精确匹配数据
        $exacRes = ExactFactory::updateExactMatchDatas($userId, $data);
        //已进行过精准匹配   直接匹配
        if (!empty($exacRes)) {
            //可以进行精准匹配
            $matchinfoArr = ExactFactory::fetchExactMatchDatas($userId);
            //获取产品id
            $data['productIdArr'] = array_column($matchinfoArr, 'productId');
            //放款时间
            $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
            //产品数据
            $productArr = ExactFactory::fetchSecondEditionExactMatchProducts($data);

            if (empty($productArr) || empty($productArr['list'])) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
            }
            //总页数
            $pageCount = $productArr['pageCount'];
            //产品标签
            $productArr = ProductFactory::tagsLimitOneToProducts($productArr['list']);

            //精确匹配结果数据处理
            $productData['list'] = ExactStrategy::getSecondEditionExactMatchDatas($productArr, $matchinfoArr, $data);
            $productData['pageCount'] = $pageCount;

            return RestResponseFactory::ok($productData);

        } else {
            //暂时没有数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
    }


}