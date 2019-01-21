<?php

namespace App\Http\Controllers\V1;

use App\Constants\ConfigConstant;
use App\Constants\CreditcardConstant;
use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Helpers\DateUtils;
use App\Helpers\RestUtils;
use App\Models\Chain\Product\ProductTag\DoProductTagHandler;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\ConfigFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\FavouriteFactory;
use App\Models\Factory\ProductFactory;
use App\Http\Controllers\Controller;
use App\Helpers\RestResponseFactory;
use App\Models\Factory\ProductPropertyFactory;
use App\Models\Factory\SystemFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * 首页诱导轮播
     * 推荐产品
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPromotions()
    {
        $promotionLists = ProductFactory::fetchPromotions();
        if (empty($promotionLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //获取申请人数
        $applyPeoples = ProductFactory::fetchTodayApplyCountByTotalTodayCount();
        //获取七牛图片
        $promotionLists = ProductStrategy::getPromotions($promotionLists, $applyPeoples, 0);
        return RestResponseFactory::ok($promotionLists);
    }

    /**
     * 新上线产品
     */
    public function fetchNewOnlines()
    {
        $onlineLists = ProductFactory::fetchNewOnlines();
        if (empty($onlineLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $onlineTags = ProductFactory::tagsByAll($onlineLists);
        //图片处理
        //首页新上线产品 引导语
        $onlineConfigNid = ProductConstant::PRODUCT_ONLINE_CONFIG;
        $onlineConfigValue = SystemFactory::fetchProductOnlineRemark($onlineConfigNid);
        $product = ProductStrategy::getNewsOlines($onlineTags, $onlineConfigValue);

        return RestResponseFactory::ok($product);
    }

    /**
     * 分类专题对应产品
     */
    public function fetchSpecials(Request $request)
    {
        $data = $request->all();
        $productType = isset($data['productType']) ? $data['productType'] : 5;
        //分类产品对应id
        $specialIds = ProductFactory::fetchSpecialId($data);
        $productIdArr = explode(',', $specialIds['product_list']);
        //分类产品
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        $specialLists = ProductFactory::fetchSpecialProducts($productIdArr, $key);
        $specialLists = ProductStrategy::getSpecialProducts($data, $productIdArr, $specialLists);

        $pageCount = $specialLists['pageCount'];
        //标签
        $specialLists = ProductFactory::tagsByAll($specialLists['list']);
        if (empty($specialLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //处理数据
        $specialLists = ProductStrategy::getSpecials($specialIds, $specialLists, $pageCount, $productType);
        return RestResponseFactory::ok($specialLists);
    }

    /**
     * @param Request $request
     * @return mixed
     * 产品搜索配置标签
     */
    public function fetchProductTagConfig()
    {
        //已申请
        $hasNid = ProductConstant::PRODUCT_TAG_TYPE_HAS;
        //已申请对应id
        $typeId = ProductFactory::fetchProductTagTypeIdByNid($hasNid);
        //已申请标签
        $hasTagIds = ProductFactory::fetchProductTagsByTagId($typeId);
        //标签数据
        $hasTags = ProductFactory::fetchSeoTagsByIds($hasTagIds);
        //数据处理
        $hasTags = ProductStrategy::getSeoTags($hasTags);

        //不符合
        $needNid = ProductConstant::PRODUCT_TAG_TYPE_NEED;
        //不符合对应id
        $typeId = ProductFactory::fetchProductTagTypeIdByNid($needNid);
        //不符合标签
        $needTagIds = ProductFactory::fetchProductTagsByTagId($typeId);
        //标签数据
        $needTags = ProductFactory::fetchSeoTagsByIds($needTagIds);
        //数据处理
        $needTags = ProductStrategy::getSeoTags($needTags);


        //我需要标签
        $tagConfig['loan_need_lists'] = isset($needTags) ? $needTags : [];
        //我有标签
        $tagConfig['loan_has_lists'] = isset($hasTags) ? $hasTags : [];

        return RestResponseFactory::ok($tagConfig);
    }

    /**
     * @param Request $request
     * @return mixed
     * 产品搜索  &&  产品列表
     */
    public function fetchProductOrSearch(Request $request)
    {
        $data = $request->all();
        //地域id
        $deviceId = $request->input('areaId', '');
        //所有产品id
        $productIds = ProductFactory::fetchProductIds();
        //产品城市关联表中的所有产品id
        $cityProductIds = DeviceFactory::fetchCityProductIds();
        //地域对应产品id
        $deviceProductIds = DeviceFactory::fetchProductIdsByDeviceId($deviceId);

        //产品列表
        $product = ProductFactory::fetchProductOrSearch($data, $deviceProductIds, $cityProductIds, $productIds, $deviceId);
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
        $productLists = ProductStrategy::productAll($productType, $productLists, $pageCount);

        return RestResponseFactory::ok($productLists);

    }

    /**
     * 产品详情——计算器
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCalculators(Request $request)
    {
        $data = $request->all();
        //产品信息
        $productArr = ProductFactory::fetchCalculators($data);
        if (empty($productArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //金额  && 期限  选取范围
        $productArr['loan_money'] = ProductStrategy::getMoneyData($productArr);
        $productArr['loan_term'] = ProductStrategy::getTermData($productArr);
        //整合数据
        $calcuLists = ProductStrategy::getCalculators($productArr);
        return RestResponseFactory::ok($calcuLists);

    }

    /**
     * 产品详情——详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchDetails(Request $request)
    {
        $data = $request->all();

        $productId = $data['productId'];
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
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

        //标签
        $productTag = ProductFactory::tagsByOne($productInfo, $productId);
        //整合数据
        $product = ProductStrategy::getDetails($productTag, $productId);
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
     * 首页推荐产品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRecommends(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 32);
        $data['terminalType'] = $request->input('terminalType', '');

        //获取推荐产品
        $recommends = ProductFactory::fetchRecommends($data);
        //没有产品数据
        if (empty($recommends['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //整理数据
        $recommendDatas['list'] = ProductStrategy::getRecommends($recommends['list']);
        $recommendDatas['pageCount'] = $recommends['pageCount'];

        return RestResponseFactory::ok($recommendDatas);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 代还信用卡产品
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
        $specialLists = ProductStrategy::getGiveBackProducts($specialLists);
        //处理数据
        return RestResponseFactory::ok($specialLists);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 还款提醒中对应的推荐产品
     * 默认3个
     */
    public function fetchAccountAlertProducts(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 3);
        $typeNid = $request->input('creditcardType', CreditcardConstant::CREGITCARD_TYPE_NID);
        //分类产品对应代还产品id
        $productIds = ProductFactory::fetchSpecialProductIdsByTypeNid($typeNid);
        $data['productIds'] = explode(',', $productIds['product_list']);
        $data['condition'] = $productIds['product_list'];
        //放款时间
        $data['key'] = ProductConstant::PRODUCT_LOAN_TIME;
        $specialLists = ProductFactory::fetchAccountAlertProducts($data);
        $pageCount = $specialLists['pageCount'];
        if (empty($specialLists) || empty($productIds)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //整理数据
        $datas['list'] = ProductStrategy::getGiveBackProducts($specialLists['list']);
        $datas['pageCount'] = $pageCount;

        return RestResponseFactory::ok($datas);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 产品申请记录
     */
    public function fetchApplyHistory(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);

        //用户产品申请记录
        $historys = ProductFactory::fetchApplyHistorysByUserId($data);
        //用户暂无产品申请记录
        if (empty($historys)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //产品申请记录产品
        $historys = ProductFactory::fetchHistoryProducts($historys);
        //判断申请记录是否有评论
        $historys = CommentFactory::fetchHistorysIsComment($historys);
        //数据转化
        $historys = ProductStrategy::getApplyHistory($historys);
        //暂无数据
        if (empty($historys)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //分页
        $historys = DateUtils::pageInfo($historys, $data['pageSize'], $data['pageNum']);

        return RestResponseFactory::ok($historys);
    }

    /**
     * 不想看产品黑名单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductBlacks(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 2);
        //不算进不想看中的ids
        $blackIdsStr = $request->input('blackIdsStr', '');
        $blackIdsStr = empty($blackIdsStr) ? [] : explode(',', $blackIdsStr);
        //所有不想看产品ids
        $blackIds = ProductFactory::fetchProductBlackIds($data);
        //和集
        $data['mergeBlackIds'] = array_merge($blackIds, $blackIdsStr);
        //黑名单产品ids
        $data['blackIds'] = ProductFactory::fetchProductBlackIdsInfo($data);
        //查询产品
        $productBlacks = ProductFactory::fetchProductBlacks($data['blackIds']);
        //暂无数据
        if (empty($productBlacks)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //分页
        $productBlacks = DateUtils::pageInfo($productBlacks, $data['pageSize'], $data['pageNum']);

        return RestResponseFactory::ok($productBlacks);
    }

    /**
     * 添加不想看产品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProductBlack(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['productId'] = $request->input('productId');

        //验证是否已经创建产品黑名单
        $status = ProductFactory::fetchProductBlackStatus($data);
        //不想看产品已添加
        if ($status == 1) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1801), 1801);
        }
        //创建产品黑名单
        $black = ProductFactory::updateProductBlack($data);
        if (!$black) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 取消产品不想看
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProductBlack(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['productId'] = $request->input('productId');

        //验证是否已经创建产品黑名单
        $status = ProductFactory::fetchProductBlackStatus($data);
        //取消产品不想看状态
        if ($status != 1) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1801), 1801);
        }

        //取消产品不想看状态
        $black = ProductFactory::deleteProductBlack($data);
        if (!$black) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 首页ROI排序产品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRoiProducts(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 3);
        //借款金额
        $data['loanAmount'] = $request->input('loanAmount', '');
        //借款期限
        $data['loanTerm'] = $request->input('loanTerm', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';

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

        //定位设备id
        $deviceId = $request->input('deviceId', '');
        //根据设备id获取城市id
        $data['deviceId'] = DeviceFactory::fetchCityIdByDeviceIdAndUserId($deviceId);
        //所有产品id
        $data['productIds'] = ProductFactory::fetchProductIds();
        //产品城市关联表中的所有产品id
        $data['cityProductIds'] = DeviceFactory::fetchCityProductIds();
        //地域对应产品id
        $data['deviceProductIds'] = DeviceFactory::fetchProductIdsByDeviceId($data['deviceId']);

        //不想看产品ids
        $data['blackIds'] = ProductFactory::fetchBlackIdsByUserId($data);

        //产品列表
        $product = ProductFactory::fetchProductsOrFilters($data);
        //暂无产品数据
        if (empty($product['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($product['list']);
        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);

        return RestResponseFactory::ok($productLists);
    }

    /**
     * 首页产品总个数
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function fetchProductCounts(Request $request)
    {
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        //贷款产品
        $data['productIds'] = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        $counts = ProductFactory::fetchProductCounts($data);
        $res['product_counts'] = $counts . '';

        return RestResponseFactory::ok($res);
    }

    /**
     * 账单模块 推荐产品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBillProductSpecials(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);

        //类型
        $data['productType'] = $request->input('productType', 2);

        //借款金额
        $data['loanAmount'] = $request->input('loanAmount', '');
        //借款期限
        $data['loanTerm'] = $request->input('loanTerm', '');
        //不想看产品ids 用字符串拼接
        $blackIdsStr = $request->input('blackIdsStr', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        //定位设备id
        $deviceId = $request->input('deviceId', '');
        //根据设备id获取城市id
        $data['deviceId'] = DeviceFactory::fetchCityIdByDeviceIdAndUserId($deviceId);
        //所有产品id
        $data['productIds'] = ProductFactory::fetchProductIds();
        //产品城市关联表中的所有产品id
        $data['cityProductIds'] = DeviceFactory::fetchCityProductIds();
        //地域对应产品id
        $data['deviceProductIds'] = DeviceFactory::fetchProductIdsByDeviceId($data['deviceId']);

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

        //不想看产品ids
        $blackIds = ProductFactory::fetchBlackIdsByUserId($data);
        //不计算进不想看的产品ids
        $blackIdsStr = empty($blackIdsStr) ? [] : explode(',', $blackIdsStr);
        //原来已存在不想看产品ids 与并不计算进不想看的ids求差集
        $data['blackIds'] = array_diff($blackIds, $blackIdsStr);

        //产品列表
        $product = ProductFactory::fetchProductsOrFilters($data);
        //暂无产品数据
        if (empty($product['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $pageCount = $product['pageCount'] >= 1 ? 1 : 0;

        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($product['list']);

        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);

        $params['list'] = $productLists;
        $params['pageCount'] = $pageCount;

        return RestResponseFactory::ok($params);
    }

    /**
     * 速贷大全 —— 会员与非会员申请量总计
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchUserVipProductCount(Request $request)
    {
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');

        //vip用户可查看产品ids
        $productVipIds = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        $data['productIds'] = $productVipIds;
        $counts = ProductFactory::fetchProductCounts($data);
        //非vip和用户可查看产品ids
        $productCommonIds = ProductFactory::fetchProductVipIds(UserVipFactory::getCommonTypeId());
        $data['productIds'] = $productCommonIds;
        $commonCounts = ProductFactory::fetchProductCounts($data);
        //vip用户与非vip用户可以看见的数据差值
        $diffCounts = bcsub($counts, $commonCounts);
        if ($diffCounts < 0) {
            $diffCounts = 0;
        }

        $params['product_vip_count'] = $counts;
        $params['product_diff_count'] = intval($diffCounts);

        return RestResponseFactory::ok($params);
    }

    /**
     * 滑动专题
     * 与会员有关
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSlideSpecials(Request $request)
    {
        $data['sign'] = $request->input('sign', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        //区分会员
        $data['userVipType'] = UserVipFactory::fetchUserVipToTypeByUserId($data['userId']);

        if ($data['userVipType']) {
            //会员
            $data['productVipIds'] = ProductFactory::fetchProductVipIdsByVipTypeId($data);
        } else {
            //普通用户
            $ordinary['userVipType'] = UserVipFactory::fetchIdByVipType(UserVipConstant::VIP_TYPE_NID_VIP_COMMON);
            $data['productVipIds'] = ProductFactory::fetchProductVipIdsByVipTypeId($ordinary);
        }

        //根据typeNid获取产品推荐配置类型表id
        $typeId = ProductFactory::fetchPlatformProductRecommendTypeIdByNid($data['sign']);
        //产品数据
        $data['typeId'] = $typeId;
        $data['productIds'] = ProductFactory::fetchRecommendIdsByTypeId($data);

        //查询产品数据
        $products = ProductFactory::fetchSlideProducts($data);
        //暂无数据
        if (!$products['list']) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $pageCount = $products['pageCount'];
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($products['list']);
        //数据处理
        $products = ProductStrategy::getProductOrSearchLists($data);

        $res['list'] = $products;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }

    /**
     * 不想看产品标签
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductBlackTags()
    {
        //已申请
        $appliedNid = ProductConstant::PRODUCT_TAG_TYPE_APPLIED;
        //已申请对应id
        $typeId = ProductFactory::fetchProductTagTypeIdByNid($appliedNid);
        //已申请标签
        $appliedTagIds = ProductFactory::fetchProductTagsByTagId($typeId);
        //标签数据
        $appliedTags = ProductFactory::fetchSeoTagsByIds($appliedTagIds);

        //不符合
        $misMatchNid = ProductConstant::PRODUCT_TAG_TYPE_MISMATCH;
        //不符合对应id
        $typeId = ProductFactory::fetchProductTagTypeIdByNid($misMatchNid);
        //不符合标签
        $misMatchTagIds = ProductFactory::fetchProductTagsByTagId($typeId);
        //标签数据
        $misMatchTags = ProductFactory::fetchSeoTagsByIds($misMatchTagIds);

        $res['applied'] = isset($appliedTags) ? $appliedTags : [];
        $res['mismatch'] = isset($misMatchTags) ? $misMatchTags : [];

        return RestResponseFactory::ok($res);
    }

    /**
     * 不想看产品标签修改
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createProductBlackTag(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['productId'] = $request->input('productId');
        $data['tagIds'] = $request->input('tagIds', '');
        $data['type'] = $request->input('type', 0);

        $data['tagIdArr'] = !empty($data['tagIds']) ? explode(',', $data['tagIds']) : [];

        $tag = new DoProductTagHandler($data);
        $re = $tag->handleRequest();
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 根据产品id获取H5注册链接地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductUrlByProductId(Request $request)
    {
        $productId = $request->input('productId','');

        //产品信息
        $info = ProductFactory::productOne($productId);
        //数据处理
        $info = ProductStrategy::getProductUrl($info);

        return RestResponseFactory::ok($info);
    }
}
