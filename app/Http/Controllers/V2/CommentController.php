<?php
namespace App\Http\Controllers\V2;

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
     * @return mixed
     * 最热评论列表
     */
    public function fetchCommentHots(Request $request)
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
        $commentIds = CommentFactory::fetchCommentHotsIds($productId, $resultId);
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
        $commentDatas = CommentStrategy::getComments($comments,$replyOffset);
        //判断用户是否已经点赞
        $commentDatas = CommentFactory::clickUseful($commentDatas, $userId);

        $datas['list'] = $commentDatas;
        $datas['pageCount'] = $pageCount;

        return RestResponseFactory::ok($datas);
    }

    /**
     * @param Request $request
     * 所有评论列表
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
        $commentDatas = CommentStrategy::getComments($comments,$replyOffset);
        //判断用户是否已经点赞
        $commentDatas = CommentFactory::clickUseful($commentDatas, $userId);

        $datas['list'] = $commentDatas;
        $datas['pageCount'] = $pageCount;

        return RestResponseFactory::ok($datas);
    }

    /**
     * @param Request $request
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
        $commentArr = CommentStrategy::updateComment($data);
        //修改数据
        $contentData = CommentFactory::updateCommentDatas($commentArr, $userId, $productId);

        if (empty($contentData)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        return RestResponseFactory::ok(RestUtils::getStdObj());
    } 


}