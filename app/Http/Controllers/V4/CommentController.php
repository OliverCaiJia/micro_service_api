<?php

namespace App\Http\Controllers\V4;

use App\Constants\CommentConstant;
use App\Constants\CreditConstant;
use App\Events\V1\AddIntegralEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\ProductFactory;
use App\Services\Core\WangYiYunDun\CloudShield\CloudShieldService;
use App\Models\Factory\ReplyFactory;
use App\Strategies\CommentStrategy;
use Illuminate\Http\Request;

/**
 * Class CommentController
 * @package App\Http\Controllers\V4
 * 评论控制器
 */
class CommentController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 最热评论
     * 添加vip用户标识
     */
    public function fetchCommentHots(Request $request)
    {
        $productId = $request->input('productId');
        //借款状态 用于筛选
        $resultId = $request->input('resultId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $pageSize = $request->input('pageSize', 1);
        $pageNum = $request->input('pageNum', 10);
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
        //回复条数 限制3条
        $replyOffset = 3;
        $comments = ReplyFactory::fetchReplysById($comments['list'], $replyOffset);
        //回复用户信息
        $comments = ReplyFactory::fetchReplysUserinfo($comments);
        //整理数据
        $commentDatas = CommentStrategy::getCommentsAndHotsDatas($comments, $replyOffset);
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
     * 添加vip用户标识
     */
    public function fetchComments(Request $request)
    {
        $productId = $request->input('productId');
        //借款状态 用于筛选
        $resultId = $request->input('resultId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $pageSize = $request->input('pageSize', 1);
        $pageNum = $request->input('pageNum', 10);

        //所有评论id
        $commentIds = CommentFactory::fetchCommentsIds($productId, $resultId);
        //没有数据
        if (empty($commentIds)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //所有评论 分页显示
        $comments = CommentFactory::fetchCommentsById($commentIds, $pageSize, $pageNum);
        $pageCount = $comments['pageCount'];
        //回复条数 限制3条
        $replyOffset = 3;
        $comments = ReplyFactory::fetchReplysById($comments['list'], $replyOffset);
        //回复用户信息
        $comments = ReplyFactory::fetchReplysUserinfo($comments);
        //整理数据
        $commentDatas = CommentStrategy::getCommentsAndHotsDatas($comments, $replyOffset);
        //判断用户是否已经点赞
        $commentDatas = CommentFactory::clickUseful($commentDatas, $userId);

        $datas['list'] = $commentDatas;
        $datas['pageCount'] = $pageCount;

        return RestResponseFactory::ok($datas);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 评论——创建或修改评论内容 加积分
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
        //网易云盾敏感词过滤
        $dataId = "产品列表评论";
        $text = CloudShieldService::ReplyMain($dataId, $userId, $data['content']);
        if ($text != 0) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1607), 1607);
        }
        //数据整合
        $commentArr = CommentStrategy::createOrUpdateComment($data);
        //创建评论加积分
        $commentId = CommentFactory::fetchCommentId($userId, $productId);
        if (!$commentId) {
            //首次设置评论加积分
            $eventData['typeNid'] = CreditConstant::ADD_INTEGRAL_USER_COMMENT_TYPE;
            $eventData['remark'] = CreditConstant::ADD_INTEGRAL_USER_COMMENT_REMARK;
            $eventData['max_count'] = CreditConstant::ADD_INTEGRAL_USER_COMMENT_COUNT;
            $eventData['typeId'] = CreditFactory::fetchIdByTypeNid($eventData['typeNid']);
            $eventData['score'] = CreditFactory::fetchScoreByTypeNid($eventData['typeNid']);
            $eventData['userId'] = $userId;
            event(new AddIntegralEvent($eventData));
        }
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
}
