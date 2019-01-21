<?php

namespace App\Http\Controllers\V3;

use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\DeviceFactory;
use App\Models\Factory\FavouriteFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductPropertyFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\CommentStrategy;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;

/**
 * 产品模块
 * Class ProductController
 * @package App\Http\Controllers\V3
 */
class ProductController extends Controller
{
    /**
     * 第三版  计算器计算
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCalculators(Request $request)
    {
        //产品id
        $productId = $request->input('productId', '');
        //额度
        $params['loanMoney'] = $request->input('loanMoney', '');
        //期限
        $params['loanTimes'] = $request->input('loanTimes', '');
        //产品详情
        $info = ProductFactory::productOne($productId);
        if (empty($info)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        $params['interest_alg'] = $info['interest_alg'];
        //产品对应费率
        $fee = ProductFactory::fetchProductFee($productId);
        if (empty($fee)) {
            //暂无数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //根据利息金额转化
        $params = ProductStrategy::getLoanTimesByInterest($params);
        //逾期费
        $params['fee'] = $fee;
        //将利率全部转化为利率费用
        $calcuCost['cost'] = ProductStrategy::getCalculatorCost($params);
        $calcuCost['overdue_alg'] = $info['overdue_alg'];
        $calcuCost['loanMoney'] = $params['loanMoney'];
        //数据格式处理，加和求总计
        $calcuTotal = ProductStrategy::getCalculatorTotal($calcuCost);

        return RestResponseFactory::ok($calcuTotal);
    }

    /**
     * 第三版 产品列表 & 速贷大全筛选
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductsOrSearchs(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //借款金额
        $data['loanAmount'] = $request->input('loanAmount', '');
        //借款期限
        $data['loanTerm'] = $request->input('loanTerm', '');
        //不想看产品ids 用字符串拼接
        $blackIdsStr = $request->input('blackIdsStr', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
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
        $list_sign = 1;
        $product = ProductFactory::fetchProductsOrFilters($data);
        $pageCount = $product['pageCount'];
        if (empty($product['list'])) {
            $data['pageSize'] = 1;
            $data['pageNum'] = 5;
            $product = ProductFactory::fetchLikeProducts($data);
            $list_sign = 0;
            $pageCount = 1;
        }

        //暂无产品数据
        if (empty($product['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($product['list']);

        //vip用户可查看产品ids
        $productVipIds = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        $data['productIds'] = $productVipIds;
        $counts = ProductFactory::fetchProductCounts($data);
        //非vip和用户可查看产品ids
        $productCommonIds = ProductFactory::fetchProductVipIds(UserVipFactory::getCommonTypeId());
        $data['productIds'] = $productCommonIds;
        $commonCounts = ProductFactory::fetchProductCounts($data);
        //处理数据
        //会员产品id作为key
        $vipCommonDiffIds = array_diff($productVipIds, $productCommonIds);
        $data['vipProductIds'] = isset($vipCommonDiffIds) ? array_flip($vipCommonDiffIds) : [];
        $productLists = ProductStrategy::getProductOrSearchLists($data);


        //vip用户与非vip用户可以看见的数据差值
        $diffCounts = bcsub($counts, $commonCounts);
        if ($diffCounts < 0) {
            $diffCounts = 0;
        }

        $params['list'] = $productLists;
        $params['pageCount'] = $pageCount;
        $params['product_vip_count'] = $counts;
        $params['list_sign'] = $list_sign;
        $params['product_diff_count'] = intval($diffCounts);

        return RestResponseFactory::ok($params);
    }

    /**
     * 第三版 产品详情第一部分
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchDetailPartOne(Request $request)
    {
        $data['productId'] = $request->input('productId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['userId'] = $userId;

        //产品详情
        $data['info'] = ProductFactory::productOne($data['productId']);
        if (empty($data['info'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        //下款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        $loanSpeed = ProductPropertyFactory::fetchPropertyValue($data['productId'], $key);
        $data['loanSpeed'] = empty($loanSpeed) ? '3600' : $loanSpeed;
        //审批条件标签
        $approval_condition = ProductConstant::PRODUCT_DETAIL_APPROVAL_CONDITION;
        $condition['type_id'] = ProductFactory::fetchApprovalConditionTypeId($approval_condition);
        $condition['productId'] = $data['productId'];
        $data['condition_tags'] = ProductFactory::fetchDetailTags($condition);
        //信用贴士标签
        $credit_tips = ProductConstant::PRODUCT_DETAIL_CREDIT_TIPS;
        $tips['type_id'] = ProductFactory::fetchApprovalConditionTypeId($credit_tips);
        $tips['productId'] = $data['productId'];
        $data['tips_tags'] = ProductFactory::fetchDetailTags($tips);
        //手机号
        $data['mobile'] = UserFactory::fetchMobile($data['userId']);
        //整合数据
        $product = ProductStrategy::getDetailPartOne($data);
        //判断是否收藏产品
        $product['sign'] = FavouriteFactory::collectionProducts($data['userId'], $data['productId']);

        //用户信息
        $user = UserFactory::fetchUserNameAndMobile($userId);
        //获取产品信息
        $products = ProductFactory::fetchProductname($data['productId']);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryIdToNull($userId);
        //获取渠道信息
        $deliverys = DeliveryFactory::fetchDeliveryArray($deliveryId);
        //访问产品详情记录流水表
        $productLog = ProductFactory::createProductLog($userId, $data, $user, $products, $deliverys);

        return RestResponseFactory::ok($product);
    }

    /**
     * 第二部分 产品详情 展示评论与同类产品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchDetailOther(Request $request)
    {
        $data['productId'] = $request->input('productId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['userId'] = $userId;
        //最热评论
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 2);
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');

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

        //产品信息
        $product = ProductFactory::productOne($data['productId']);
        if (empty($product)) {
            //出错啦,请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //产品评论置顶消息
        $commentCounts = CommentFactory::commentCounts($data['productId']);
        //评论分类总数
        $params['score'] = number_format($product['satisfaction'], 1);
        $params['counts'] = CommentStrategy::getCommentCounts($commentCounts);
        //置顶评论ids
        $commentTopIds = CommentFactory::fetchCommentTopIds($data['productId']);
        //没有数据
        if (empty($commentTopIds)) {
            $params['comment_list'] = [];
        } else {
            $data['commentIds'] = $commentTopIds;
            //所有评论 分页显示
            $comments = CommentFactory::fetchDetailCommentsById($data);
            //整理数据
            $commentDatas = CommentStrategy::getDetailComments($comments['list']);
            $params['comment_list'] = $commentDatas;
        }

        //推荐产品
        //vip用户可查看产品ids
        $productVipIds = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        //非vip和用户可查看产品ids
        $productCommonIds = ProductFactory::fetchProductVipIds(UserVipFactory::getCommonTypeId());
        //处理数据
        //会员产品id作为key
        $vipCommonDiffIds = array_diff($productVipIds, $productCommonIds);
        $data['vipProductIds'] = isset($vipCommonDiffIds) ? array_flip($vipCommonDiffIds) : [];
        $likeProduct = ProductFactory::fetchLikeProducts($data);

        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($likeProduct['list']);
        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);
        $params['like_list'] = $productLists;

        return RestResponseFactory::ok($params);
    }

}