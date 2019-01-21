<?php

namespace App\Http\Controllers\V2;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\ReplyFactory;
use App\Strategies\CommentStrategy;
use App\Strategies\ReplyStrategy;
use App\Services\Core\WangYiYunDun\CloudShield\CloudShieldService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class ReplyController
 * @package App\Http\Controllers\V1
 * 评论回复
 */
class ReplyController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 多层回复功能  创建回复
     */
    public function createReply(Request $request)
    {
        $data['parentReplyId'] = $request->input('parentReplyId', 0);
        $data['commentId'] = $request->input('commentId');
        $data['content'] = $request->input('content');
        $data['userId'] = $request->user()->sd_user_id;

        //敏感词过滤
        $content = CommentStrategy::sensitiveWordFilter($data['content']);
        if (!empty($content)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1607), 1607);
        }
        $dataId = "产品列表评论回复";
        $reply = CloudShieldService::ReplyMain($dataId, $data['userId'], $data['content']);
        if($reply != 0){
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1607), 1607);
        }
        //创建回复
        $reply = ReplyFactory::createReplys($data);
        if (!$reply) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //回复点击统计
        ReplyFactory::updateReplyCount($data['commentId']);
        if (!empty($data['parentReplyId'])) {
            ReplyFactory::updateDateCommentReplyCount($data);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 多级回复  获取回复列表
     */
    public function fetchReplysByCommentId(Request $request)
    {
        //评论id
        $commentId = $request->input('commentId');
        $data['replyType'] = $request->input('replyType', 0);
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        $data['userId'] = $userId;

        //楼主评论
        $comment = CommentFactory::fetchCommentHotById($commentId);
        if (empty($comment)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //楼主评论数据处理
        $comment = CommentStrategy::getCommentLandlord($comment);
        //是否点赞
        $comment = CommentFactory::clickUsefulSingle($comment, $userId);
        //回复楼主
        $data['platform_comment_id'] = $comment['platform_comment_id'];

        //根据回复点赞量与回复总量之和 筛选出回复id
        //$data['replyIds'] = ReplyFactory::fetchReplyIdsByCount($data['platform_comment_id']);
        $replys = ReplyFactory::fetchReplyLandlords($data);
        $pageCount = $replys['pageCount'];

        if ($replys['list']) {
            //回复父级数据
            $replys = ReplyFactory::fetchParentReplys($replys['list']);
            //判断是否点赞
            $replys = ReplyFactory::clickUseful($replys, $data);
            //回复数据处理
            $replys = ReplyStrategy::getCommentReplys($replys);

        } else {
            $replys = [];
        }
        $datas['list'] = array_values($replys);
        $datas['pageCount'] = $pageCount;
        $comment['replys'] = $datas;

        return RestResponseFactory::ok($comment);
    }


}