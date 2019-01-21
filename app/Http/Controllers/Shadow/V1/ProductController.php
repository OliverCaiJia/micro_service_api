<?php

namespace App\Http\Controllers\Shadow\V1;

use App\Events\Shadow\ShadowProductApplyEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\ShadowFactory;
use App\Models\Factory\UserFactory;
use App\Strategies\CommentStrategy;
use Illuminate\Http\Request;
use App\Models\Factory\ProductFactory;
use App\Strategies\ProductStrategy;

class ProductController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * 产品详情——点击借款
     */
    public function apply(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        $data['userId'] = $userId;

        // 获取平台网址
        $url = PlatformFactory::fetchShadowProductUrl($data);
        if (empty($url)) {
            $url = PlatformFactory::fetchProductUrl($data);
        }

        // 获取产品信息
        $product = ProductFactory::fetchProduct($data['productId']);
        // 获取用户信息
        $user = UserFactory::fetchUserNameAndMobile($userId);
        // 获取渠道id sd_user_shadow
        $deliveryId = ShadowFactory::getDeliveryId($userId);
        // 获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);
        // 获取shadow id
        $shadowId = ShadowFactory::getShadowId($userId);
        // 获取shadow nid
        $shadowNid = ShadowFactory::getShadowNid($shadowId);

        if (empty($user) || empty($product) || empty($shadowId) || empty($deliveryArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //数据处理
        $data = [
            'user_id' => $userId,
            'username' => $user['username'],
            'mobile' => $user['mobile'],
            'platform_id' => $data['platformId'],
            'platform_product_id' => $data['productId'],
            'platform_product_name' => $product['platform_product_name'],
            'channel_id' => $deliveryArr['id'],
            'channel_title' => $deliveryArr['title'],
            'channel_nid' => $deliveryArr['nid'],
            'shadow_nid' => empty($shadowNid) ? 'sudaizhijia' : $shadowNid,
            'user_agent' => UserAgent::i()->getUserAgent(),
            'create_at' => date('Y-m-d H:i:s', time()),
            'create_ip' => Utils::ipAddress(),
        ];

        // 立即申请触发事件记录流水
        event(new ShadowProductApplyEvent($data));

        return RestResponseFactory::ok(['url' => $url]);
    }

    /** 马甲速贷大全列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductsOrSearchs(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        //产品列表
        $product = ProductFactory::fetchProductsShadow($data);

        //产品查看类型 0全部,1:iOS,2:Android,3:WEB
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
     * 借钱360第二版 产品列表 & 速贷大全筛选
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductsOrFilters(Request $request)
    {
        $data = $request->all();
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //借款金额
        $data['loanAmount'] = $request->input('loanAmount', '0,');
        //借款期限
        $data['loanTerm'] = $request->input('loanTerm', '0,');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;

        //产品列表
        $list_sign = 1;
        $product = ProductFactory::fetchProductsShadow($data);
        $pageCount = $product['pageCount'];
        if (empty($product['list'])) {
            $data['productType'] = 1;
            $data['pageSize'] = 1;
            $data['pageNum'] = 5;
            $product = ProductFactory::fetchProductsShadow($data);
            $list_sign = 0;
            $pageCount = 1;
        }

        //暂无产品数据
        if (empty($product['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($product['list']);

        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);
        $params['list'] = $productLists;
        $params['pageCount'] = $pageCount;
        $params['list_sign'] = $list_sign;

        return RestResponseFactory::ok($params);
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

        //产品信息
        $product = ProductFactory::productOne($data['productId']);
        if (empty($product)) {
            //出错啦,请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //产品评论置顶消息
        $commentCounts = CommentFactory::commentCounts($data['productId']);
        //评论分类总数
        $params['score'] = $product['satisfaction'] . '';
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
        $data['productType'] = 1;
        $data['pageSize'] = 1;
        $data['pageNum'] = 2;
        $likeProduct = ProductFactory::fetchProductsShadow($data);
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($likeProduct['list']);
        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);
        $params['like_list'] = $productLists;

        return RestResponseFactory::ok($params);
    }
}