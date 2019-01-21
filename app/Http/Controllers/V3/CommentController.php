<?php

namespace App\Http\Controllers\V3;

use App\Constants\CommentConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ReplyFactory;
use App\Strategies\CommentStrategy;
use Illuminate\Http\Request;

/**
 * Class CommentController
 * @package App\Http\Controllers\V1
 * 评论
 */
class CommentController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @desc    评论——产品标签+评论分数
     * composite_rate:[string] 产品利率满意度
     * experience:[string] 用户体验满意度
     * loan_speed:[string] 放贷速度满意度
     * score :[string] 产品综合评分
     * 7已批贷 , 2 被拒绝 , 4 等待审批 , 6 未申请
     */
    public function fetchCommentCountAndScore(Request $request)
    {
        $productId = $request->input('productId');

        //产品信息
        $data['product'] = ProductFactory::productOne($productId);
        if (empty($data['product'])) {
            //出错啦,请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //评论分类总数
        //评论总数
        $data['counts'] = CommentFactory::commentCounts($productId);
        //已批贷总个数 result = 7
        $data['approvedLoanCounts'] = CommentFactory::fetchResultCounts($productId, 7);
        //被拒绝总个数 result = 2
        $data['failCounts'] = CommentFactory::fetchResultCounts($productId, 2);
        //等待审批总个数 result = 4
        $data['applicationCounts'] = CommentFactory::fetchResultCounts($productId, 4);
        //未申请总个数 result = 6
        $data['noApplyCounts'] = CommentFactory::fetchResultCounts($productId, 6);
        //数据整理
        $comment = CommentStrategy::fetchCommentCountsAndScore($data);
        $comment['count_all'] = $data['counts'];

        return RestResponseFactory::ok($comment);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 评论——在添加之前获取评论内容
     */
    public function fetchCommentsBefore(Request $request)
    {
        $productId = $request->input('productId');
        $userId = $request->user()->sd_user_id;

        //获取评论内容
        $comment = CommentFactory::fetchCommentsBefore($userId, $productId);
        $commentId = empty($comment) ? '' : $comment['platform_comment_id'];
        //获取审批时间
        $key = CommentConstant::APPLY_TIME;
        $applyTime = CommentFactory::fetchApplyTime($commentId, $key);
        //获取产品名称&logo
        $product = ProductFactory::getProductLogoAndName($productId);
        if (empty($product)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //数据处理
        $commentDatas = CommentStrategy::getCommentsBefore($comment, $product, $applyTime, $productId);

        return RestResponseFactory::ok($commentDatas);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 评论——创建或修改评论内容
     */
    public function createOrUpdateComment(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        $productId = $data['productId'];

        //判断金额
        $money = ProductFactory::fetchLoanMoneyById($productId);
        $isRange = CommentFactory::isCheckRange($data['loanMoney'], $money);
        if (!$isRange) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1609), 1609);
        }

        //敏感词过滤
        $content = CommentStrategy::sensitiveWordFilter($data['content']);
        if (!empty($content)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1607), 1607);
        }

        //数据整合
        $commentArr = CommentStrategy::createOrUpdateComment($data);
        //修改评论数据
        $contentData = CommentFactory::createOrUpdateComment($commentArr, $userId, $productId);
        //获取评论id
        $commentId = CommentFactory::fetchCommentId($userId, $productId);
        //修改评论特性审批时间数据
        $key = CommentConstant::APPLY_TIME;
        $property = CommentFactory::createOrUpdateCommentProperty($commentId, $key, $data);

        if (empty($contentData) || empty($property)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 最热评论
     */
    public static function fetchCommentHots(Request $request)
    {
        $productId = $request->input('productId');
        //借款状态 用于筛选
        $resultId = $request->input('resultId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $pageSize = $request->input('pageSize', 1);
        $pageNum = $request->input('pageNum', 10);
        $pageNum = empty($pageNum) ? 10 : $pageNum;

        /**
         * 最热评论列表 显示10个
         */
        //最热评论id
        //近30天点赞评论id
        $commentUsefulIds = CommentFactory::fetchCommentUsefulIdsByUseful($productId, $resultId);
        //近30天回复评论id
        $commentReplyIds = ReplyFactory::fetchCommentReplyIdsByReply($productId, $resultId);
        //求并集
        $mergeIds = array_merge($commentUsefulIds, $commentReplyIds);
        //点赞数和评论数之和 大于 2 的所有id
        $commentAllIds = CommentFactory::fetchCommentHotsIds($productId, $resultId);
        //求交集
        $commentIds = array_intersect($mergeIds, $commentAllIds);
        //没有数据
        if (empty($commentIds)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //所有评论 分页显示
        $comments = CommentFactory::fetchCommentsById($commentIds, $pageSize, $pageNum);
        $pageCount = $comments['pageCount'];
        //回复条数 限制5条
        $replyOffset = 5;
        $comments = ReplyFactory::fetchReplyCommentsById($comments['list'], $replyOffset);

        //整理数据
        $commentDatas = CommentStrategy::getCommentsAndHots($comments, $replyOffset);
        //判断用户是否已经点赞
        $commentDatas = CommentFactory::clickUseful($commentDatas, $userId);

        $datas['list'] = $commentDatas;
        $datas['pageCount'] = $pageCount;

        return RestResponseFactory::ok($datas);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 最新评论 —— 所有评论列表
     */
    public function fetchComments(Request $request)
    {
        $productId = $request->input('productId');
        //借款状态 用于筛选
        $resultId = $request->input('resultId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $pageSize = $request->input('pageSize', 1);
        $pageNum = $request->input('pageNum', 10);
        $pageNum = empty($pageNum) ? 10 : $pageNum;

        //所有评论id
        $commentIds = CommentFactory::fetchCommentsIds($productId, $resultId);
        //没有数据
        if (empty($commentIds)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //所有评论 分页显示
        $comments = CommentFactory::fetchCommentsById($commentIds, $pageSize, $pageNum);
        $pageCount = $comments['pageCount'];
        //回复条数 限制5条
        $replyOffset = 5;
        $comments = ReplyFactory::fetchReplyCommentsById($comments['list'], $replyOffset);

        //整理数据
        $commentDatas = CommentStrategy::getCommentsAndHots($comments, $replyOffset);
        //判断用户是否已经点赞
        $commentDatas = CommentFactory::clickUseful($commentDatas, $userId);

        $datas['list'] = $commentDatas;
        $datas['pageCount'] = $pageCount;

        return RestResponseFactory::ok($datas);
    }


}