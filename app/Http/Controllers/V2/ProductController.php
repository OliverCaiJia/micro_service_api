<?php

namespace App\Http\Controllers\V2;

use App\Constants\CreditcardConstant;
use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\FavouriteFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductPropertyFactory;
use App\Models\Factory\SystemFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;

/**
 * Class ProductController
 * @package App\Http\Controllers\V2
 * 产品相关
 */
class ProductController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 产品详情——计算器
     */
    public function fetchCalculators(Request $request)
    {
        $productId = $request->input('productId');

        //获取倍率值
        $key = ProductConstant::PRODUCT_TIMES;
        $times = ProductPropertyFactory::fetchPropertyValue($productId, $key);

        //产品信息
        $products = ProductFactory::fetchCounter($productId);
        if (empty($products)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        //金额  && 期限  选取范围
        $products['loan_money'] = ProductStrategy::getMoneyData($products);
        $products['loan_term'] = ProductStrategy::getTermData($products);
        //整合数据
        $calcuLists = ProductStrategy::getCounter($products, $times);
        return RestResponseFactory::ok($calcuLists);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 产品详情——详情 第一部分
     */
    public function fetchDetails(Request $request)
    {
        $data = $request->all();
        $productId = $request->input('productId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';

        //产品详情
        $productInfo = ProductFactory::productOne($productId);
        if (empty($productInfo)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //下款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        $loanSpeed = ProductPropertyFactory::fetchPropertyValue($productId, $key);
        $loanSpeed = empty($loanSpeed) ? '3600' : $loanSpeed;
        //标签
        $productTag = ProductFactory::tagsByOne($productInfo, $productId);
        $productTag['commentCounts'] = CommentFactory::commentAllCount($productId);

        //整合数据
        $product = ProductStrategy::getDetail($productTag, $productId, $loanSpeed);
        //判断是否收藏产品
        $product['sign'] = FavouriteFactory::collectionProducts($userId, $productId);

        //用户信息
        $user = UserFactory::fetchUserNameAndMobile($userId);
        //获取产品信息
        $products = ProductFactory::fetchProductname($productId);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryIdToNull($userId);
        //获取渠道信息
        $deliverys = DeliveryFactory::fetchDeliveryArray($deliveryId);
        //访问产品详情记录流水表
        $productLog = ProductFactory::createProductLog($userId, $data, $user, $products, $deliverys);

        return RestResponseFactory::ok($product);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 产品详情 第二部分
     */
    public function fetchProductDetails(Request $request)
    {
        $productId = $request->input('productId', '');

        //产品详情
        $productInfo = ProductFactory::productOne($productId);
        if (empty($productInfo)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        //是否查征信 credit_investigation 1是 0否
        $creditKey = 'credit_investigation';
        $creditValue = ProductPropertyFactory::fetchProductPropertyValue($productId, $creditKey);
        //能否提额 raise_quota 1能 0否
        $raiseKey = 'raise_quota';
        $raiseValue = ProductPropertyFactory::fetchProductPropertyValue($productId, $raiseKey);
        $productInfo['credit_investigation'] = $creditValue;
        $productInfo['raise_quota'] = $raiseValue;

        //整合数据
        $product = ProductStrategy::getProductDetails($productInfo, $productId);

        return RestResponseFactory::ok($product);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 产品列表 & 速贷大全筛选
     */
    public function fetchProductsOrSearchs(Request $request)
    {
        $data = $request->all();
        //地域id
        $areaId = $request->input('areaId', '');
        //设备id
        $deviceId = $request->input('deviceId', '');
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        //用户id
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        //根据设备id与用户id获取城市id
        $cityId = DeviceFactory::fetchCityIdByDeviceIdAndUserId($deviceId);
        $data['deviceId'] = !empty($cityId) ? $cityId : $areaId;

        //所有产品id
        $data['productIds'] = ProductFactory::fetchProductIds();
        //产品城市关联表中的所有产品id
        $data['cityProductIds'] = DeviceFactory::fetchCityProductIds();
        //地域对应产品id
        $data['deviceProductIds'] = DeviceFactory::fetchProductIdsByDeviceId($data['deviceId']);

        //产品列表
        $product = ProductFactory::fetchProductsOrSearchs($data);
        //产品查看类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;

        $pageCount = $product['pageCount'];
        //标签
        $productLists = ProductFactory::tagsByAll($product['list']);
        //暂无产品
        if (empty($productLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //处理数据
        $productLists = ProductStrategy::getProductsOrSearchs($productType, $productLists, $pageCount);

        return RestResponseFactory::ok($productLists);

    }

    /**
     * 第二版 首页今日推荐
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRecommends(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        $data['terminalType'] = $request->input('terminalType', '');

        //良心推荐产品类型
        $data['typeNid'] = ProductConstant::PRODUCT_RECOMMEND;
        //对应类型id
        $data['typeId'] = ProductFactory::fetchPlatformProductRecommendTypeIdByNid($data['typeNid']);
        //今日良心推荐产品id
        $data['recommendIds'] = ProductFactory::fetchRecommendIdsByTypeId($data);
        //查询产品数据
        $data['limit'] = 2;
        $recommends['list'] = ProductFactory::fetchSecondEditionRecommends($data);
        //数据处理
        $recommends['mobile'] = $data['mobile'];
        $recommends = ProductStrategy::getSecondEditionRecommends($recommends);
        if (empty($recommends)) {
            //暂无数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($recommends);
    }

    /**
     * 分类专题对应产品列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSpecials(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        $data['productType'] = isset($data['productType']) ? $data['productType'] : 1;
        $data['specialId'] = $request->input('specialId', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //手机号
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //分类产品对应id
        $specialIds = ProductFactory::fetchSpecialId($data);
        $productIds = explode(',', $specialIds['product_list']);

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

        //分类产品
        //放款时间
        $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
        $data['productIds'] = $productIds;
        $data['specialLists'] = ProductFactory::fetchProductSpecials($data);
        //暂无数据
        if (empty($data['specialLists'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        $specialLists = ProductStrategy::getProductSpecials($data);
        $res['pageCount'] = $specialLists['pageCount'];
        //标签
        $res['specialLists'] = ProductFactory::tagsLimitOneToProducts($specialLists['list']);
        //处理数据
        $res['specialIds'] = $specialIds;
        $res['productType'] = $data['productType'];
        $res['mobile'] = $data['mobile'];
        $specialLists = ProductStrategy::getSpecialLists($res);

        return RestResponseFactory::ok($specialLists);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 代还账单产品
     */
    public function fetchGiveBackProducts(Request $request)
    {
        $typeNid = $request->input('creditcardType', CreditcardConstant::CREGITCARD_TYPE_NID);
        //分类产品对应代还产品id
        $productIds = ProductFactory::fetchSpecialProductIdsByTypeNid($typeNid);
        $data['productIds'] = explode(',', $productIds['product_list']);
        $data['condition'] = $productIds['product_list'];
        //放款时间
        $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
        //dd($data);
        $specialLists = ProductFactory::fetchSpecialProductsByTypeNid($data);
        if (empty($specialLists) || empty($productIds)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $specialLists = ProductStrategy::getBillGiveBackProducts($specialLists);
        //处理数据
        return RestResponseFactory::ok($specialLists);
    }

    /**
     * 产品搜索配置标签
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSearchProductTags()
    {
        //已申请
        $hasNid = ProductConstant::PRODUCT_TAG_TYPE_HAS;
        //已申请对应id
        $typeId = ProductFactory::fetchProductTagTypeIdByNid($hasNid);
        //已申请标签
        $hasTagIds = ProductFactory::fetchProductTagsByTagId($typeId);
        //标签数据
        $hasTags = ProductFactory::fetchSeoTagsByIds($hasTagIds);

        //不符合
        $needNid = ProductConstant::PRODUCT_TAG_TYPE_NEED;
        //不符合对应id
        $typeId = ProductFactory::fetchProductTagTypeIdByNid($needNid);
        //不符合标签
        $needTagIds = ProductFactory::fetchProductTagsByTagId($typeId);
        //标签数据
        $needTags = ProductFactory::fetchSeoTagsByIds($needTagIds);


        //我需要标签
        $tagConfig['loan_need_lists'] = isset($needTags) ? $needTags : [];
        //我有标签
        $tagConfig['loan_has_lists'] = isset($hasTags) ? $hasTags : [];

        return RestResponseFactory::ok($tagConfig);
    }
}
