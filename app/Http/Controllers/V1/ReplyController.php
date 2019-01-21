<?php
namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\ReplyFactory;
use App\Strategies\CommentStrategy;
use Illuminate\Http\Request;

/**
 * Class ReplyController
 * @package App\Http\Controllers\V1
 * 评论回复
 */
class ReplyController extends Controller
{
    /**
     * @param Request $request
     * 创建回复
     */
    public function createReply(Request $request)
    {
        $data['content'] = $request->input('content');
        $data['commentId'] = $request->input('commentId');
        $data['userId'] = $request->user()->sd_user_id;

        //敏感词过滤
        $content = CommentStrategy::sensitiveWordFilter($data['content']);
        if (!empty($content)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1607), 1607);
        }

        //创建回复
        $reply = ReplyFactory::createReply($data);
        if (!$reply) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //回复点击统计
        ReplyFactory::updateReplyCount($data['commentId']);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 获取回复列表
     */
    public function fetchReplysByCommentId(Request $request)
    {
        //评论id
        $commentId = $request->input('commentId');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $pageSize = $request->input('pageSize', 1);
        $pageNum = $request->input('pageNum', 10);

        //楼主评论
        $comment = CommentFactory::fetchCommentHotById($commentId);
        if (empty($comment)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //楼主评论数据处理
        $comment = CommentStrategy::getCommentSingle($comment);
        //是否点赞
        $comment = CommentFactory::clickUsefulSingle($comment, $userId);
        //回复楼主
        $replys = ReplyFactory::fetchReplyComments($comment['platform_comment_id'], $pageSize, $pageNum);

        $comment['replys'] = $replys;

        return RestResponseFactory::ok($comment);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 多级回复  回复点赞
     */
    public function replyClickuseful(Request $request)
    {
        $data['replyId'] = $request->input('replyId');
        $data['userId'] = $request->user()->sd_user_id;

        //判断是否点击过
        $data['status'] = 0;
        $is_click = ReplyFactory::checkIsReplyClickuseful($data);
        if (!empty($is_click)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1604), 1604);
        }

        //回复点赞
        $useful = ReplyFactory::createOrupdateUsecount($data);
        if (empty($useful)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1608), 1608);
        }

        //sd_data_platform_comment_reply_count 中的use_count
        $usecount = ReplyFactory::increReplyUsecountById($data);
        if (empty($usecount)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1608), 1608);
        }

        //点赞成功
        return RestResponseFactory::ok(['is_useful' => 1]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 多级回复 取消点赞
     */
    public function deleteReplyClickuseful(Request $request)
    {
        $data['replyId'] = $request->input('replyId');
        $data['userId'] = $request->user()->sd_user_id;

        //判断是否点击过
        $data['status'] = 1;
        $is_click = ReplyFactory::checkIsReplyClickuseful($data);
        if (!empty($is_click)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1604), 1604);
        }

        //取消点赞
        $useful = ReplyFactory::deleteUsecount($data);
        if (empty($useful)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1608), 1608);
        }

        //sd_data_platform_comment_reply_count 中的use_count
        $usecount = ReplyFactory::decreReplyUsecountById($data);
        if (empty($usecount)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1608), 1608);
        }

        //取消点赞成功
        return RestResponseFactory::ok(['is_useful' => 0]);

    }
}