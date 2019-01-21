<?php
namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\ProductFactory;
use App\Strategies\CommentStrategy;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * @desc    评论——产品标签+评论分数
     * composite_rate:[string] 产品利率满意度
     * experience:[string] 用户体验满意度
     * loan_speed:[string] 放贷速度满意度
     * score :[string] 产品综合评分
     */
    public function fetchCommentScore(Request $request)
    {
        $productId = $request->input('productId');

        //产品信息
        $product = ProductFactory::productOne($productId);
        if (empty($product)) {
            //出错啦,请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //产品对应的标签
        $productArr = ProductFactory::tagsByOne($product, $productId);
        //数据处理
        $productArr = CommentStrategy::getCommentScore($productArr, $productId);

        return RestResponseFactory::ok($productArr);
    }

    /**
     * @param Request $request
     * 评论——评论列表
     */
    public function fetchCommentLists(Request $request)
    {
        $data = $request->all();
        $productId = $data['productId'];
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $commentType = isset($data['commentType']) ? intval($data['commentType']) : 0;
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;

        $count = 0;
        switch ($commentType) {
            case 0:
                //有价值的评论id
                $commentId = CommentFactory::fetchCommentValueId($productId);
                //有价值评论总个数
                $count = CommentFactory::commentValueCount($productId);
                break;
            case 1:
                //所有评论id
                $commentId = CommentFactory::fetchCommentAllId($productId);
                //所有评论总个数
                $count = CommentFactory::commentAllCount($productId);
                break;
        }
        if (empty($commentId)) {
            //出错啦,请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //评论数据
        $commentArr = CommentFactory::fetchCommentById($commentId, $pageSize, $pageNum);

        $pageCount = $commentArr['pageCount'];
        //整合数据
        $commentArr = CommentStrategy::getCommentValueLists($commentArr['list']);

        //判断用户是否已经点赞
        $commentArr = CommentFactory::clickUseful($commentArr, $userId);
        $commentDatas['list'] = $commentArr;
        $commentDatas['pageCount'] = $pageCount;
        $commentDatas['count'] = $count;

        return RestResponseFactory::ok($commentDatas);
    }

    /**
     * @param Request $request
     * 评论——在添加之前获取评论内容
     */
    public function fetchCommentDatas(Request $request)
    {
        $productId = $request->input('productId');
        $userId = $request->user()->sd_user_id;

        //获取评论内容
        $comment = CommentFactory::fetchCommentDatas($userId, $productId);
        //获取产品名称&logo
        $product = ProductFactory::getProductLogoAndName($productId);
        //数据处理
        $commentDatas = CommentStrategy::getCommentDatas($comment, $product);

        return RestResponseFactory::ok($commentDatas);
    }

    /**
     * @param Request $request
     * 评论——创建或修改评论内容
     */
    public function updateCommentDatas(Request $request)
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
        $commentArr = CommentStrategy::updateCommentDatas($data);
        //修改数据
        $contentData = CommentFactory::updateCommentDatas($commentArr, $userId, $productId);

        if (empty($contentData)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1), 1);
        }
        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * 评论——点赞
     */
    public function createCommentUserful(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        $commentId = $data['commentId'];

        //判断是否点击过
        $status = 0;
        $is_comment = CommentFactory::getCommentUserful($userId, $commentId, $status);
        if (!empty($is_comment)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1604), 1604);
        }

        //新增评论点赞数据
        $useful = CommentFactory::updateCommentUserful($userId, $commentId);
        if (empty($useful)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1608), 1608);
        }

        //更新sd_platform_comment 中的use_count
        $platformComment = CommentFactory::increUsecommentById($commentId);
        if (empty($platformComment)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1608), 1608);
        }

        //点赞成功
        return RestResponseFactory::ok(['is_useful' => 1]);
    }

    /**
     * @param Request $request
     * 评论——取消点赞
     */
    public function deleteCommentUserful(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        $commentId = $data['commentId'];

        //判断是否点击过
        $status = 1;
        $is_comment = CommentFactory::getCommentUserful($userId, $commentId, $status);
        if (!empty($is_comment)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1604), 1604);
        }

        //减少评论点赞数据
        $useful = CommentFactory::deleteCommentUserful($userId, $commentId, $status);
        if (empty($useful)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1608), 1608);
        }
        //更新sd_platform_comment 中的use_count
        $platformComment = CommentFactory::decreUsecommentById($commentId);
        if (empty($platformComment)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1608), 1608);
        }

        //取消点赞成功
        return RestResponseFactory::ok(['is_useful' => 0]);
    }


    /**
     * @param Request $request
     * 最热评论
     * const RESULT_SUCCESS = '以下款';
     * const RESULT_FAIL = '被拒绝';
     * const RESULT_OTHER = '其他';
     * const RESULT_APPLICATION = '已申请';
     * const RESULT_APPROVED = '出额度';
     */
    public function fetchCommentCounts(Request $request)
    {
        $productId = $request->input('productId');

        /**
         * 评论数量计算
         */
        //评论总数
        $counts = CommentFactory::commentCounts($productId);
        //已申请总个数 result = 4
        $applicationCounts = CommentFactory::fetchResultCounts($productId, 4);
        //出额度总个数 result = 5
        $approvedCounts = CommentFactory::fetchResultCounts($productId, 5);
        //以下款总个数 result = 1
        $successCounts = CommentFactory::fetchResultCounts($productId, 1);
        //被拒绝总个数 result = 2
        $failCounts = CommentFactory::fetchResultCounts($productId, 2);
        //其他总个数 result = 3
        $otherCounts = CommentFactory::fetchResultCounts($productId, 3);
        //数据整理
        $commentCounts = CommentStrategy::fetchCommentCounts($successCounts, $failCounts,
            $otherCounts, $applicationCounts, $approvedCounts);
        
        $commentDatas['count'] = $counts;
        $commentDatas['list'] = $commentCounts;

        return RestResponseFactory::ok($commentDatas);

    }

}