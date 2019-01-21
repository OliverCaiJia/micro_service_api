<?php

namespace App\Http\Controllers\V5;

use App\Constants\ProductConstant;
use App\Constants\UserVipConstant;
use App\Events\V1\DataProductTagLogEvent;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\CommentStrategy;
use App\Strategies\ProductStrategy;
use Illuminate\Http\Request;

/**
 * 产品模块
 * Class ProductController
 * @package App\Http\Controllers\V4
 */
class ProductController extends Controller
{
    /**
     * 产品详情 - 产品特色
     * 相似产品推荐  千人千面
     * 根据标签匹配规则推荐产品
     * 没有产品默认展示top2
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchDetailProductLike(Request $request)
    {
        $data['productId'] = $request->input('productId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['userId'] = $userId;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //最热评论
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 2);
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');
        //定位设备号
        $data['deviceNum'] = $request->input('deviceId', '');
        //马甲标识
        $data['shadow_nid'] = $request->input('shadowNid', 'sudaizhijia');

        //标签筛选产品
        $data['matchIds'] = ProductFactory::fetchProductTagMatch($data['productId']);
        //区分会员、定位、不想看，获取最终展示产品ids
        $data['productIds'] = ProductFactory::fetchFilterProductIdsByConditions($data);
        //查询登录成功之后用户会员信息
        $data['vip_nid'] = UserVipConstant::VIP_TYPE_NID;
        $data['vip_sign'] = UserVipFactory::checkIsVip($data);


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

        //区分会员产品标志的产品id
        $data['vipProductIds'] = ProductFactory::fetchDivisionProductIds();

        $likeProduct = ProductFactory::fetchProductListsByTagMatchs($data);
        if (empty($likeProduct['list'])) {
            //查询推荐的top2
            //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
            $data['delProIds'] = ProductFactory::fetchDelQualifyProductIds($data);
            //筛选产品id集合
            $data['productIds'] = ProductFactory::fetchProductOrSearchIds($data);
            $likeProduct = ProductFactory::fetchLikeProductOrSearchs($data);
        }
        //标签
        $data['list'] = ProductFactory::tagsLimitOneToProducts($likeProduct['list']);
        //处理数据
        $productLists = ProductStrategy::getProductOrSearchLists($data);
        $params['like_list'] = $productLists;

        //流水监听
        $data['list'] = $productLists;
        $data['from'] = ProductConstant::PRODUCT_TAG_RULE_DETAIL_FROM;
        event(new DataProductTagLogEvent($data));

        return RestResponseFactory::ok($params);
    }

    /**
     * 第五版 产品列表 & 速贷大全筛选
     * 所有产品都展示
     * 非登录，非会员：所有vip在下，一个特殊的vip产品可以随意排序位置
     * 会员登录：vip混排，只要符合相应的筛选规则即可
     * “n人今日申请”更换为“n位会员今日申请”
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
        $data['blackIdsStr'] = $request->input('blackIdsStr', '');
        //用户id
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : 0;
        $data['mobile'] = isset($request->user()->mobile) ? $request->user()->mobile : '';
        //定位设备id
        $data['deviceNum'] = $request->input('deviceId', '');

        //参与撞库 不符合模式规则或者不符合资质的所有产品id集合
        $data['delProIds'] = ProductFactory::fetchDelQualifyProductIds($data);
        //筛选产品id集合
        $data['productIds'] = ProductFactory::fetchNoDiffVipProductOrSearchIds($data);
        //查询登录成功之后用户会员信息
        $data['vip_nid'] = UserVipConstant::VIP_TYPE_NID;
        $data['vip_sign'] = UserVipFactory::checkIsVip($data);

        //产品列表
        $list_sign = 1;
        $product = ProductFactory::fetchNoDiffVipProductsOrSearchs($data);
        $pageCount = $product['pageCount'];
        if (empty($product['list'])) {
            $data['pageSize'] = 1;
            $data['pageNum'] = 5;
            $product = ProductFactory::fetchLikeProductOrSearchs($data);
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
        $params['bottom_des'] = ProductConstant::BOTTOM_DES;
        $params['is_vip'] = $data['vip_sign'];


        return RestResponseFactory::ok($params);
    }

}