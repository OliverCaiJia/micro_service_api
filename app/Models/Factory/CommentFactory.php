<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\CommentReplyUseful;
use App\Models\Orm\CommentSupport;
use App\Models\Orm\CommentUseful;
use App\Models\Orm\PlatformComment;
use App\Models\Orm\PlatformCommentProperty;
use App\Strategies\PageStrategy;
use Illuminate\Support\Facades\DB;
use SoapBox\Formatter\ArrayHelpers;

class CommentFactory extends AbsModelFactory
{

    /**
     * @param $productId
     * @return array
     * 评论——评论所有id
     */
    public static function fetchCommentAllId($productId)
    {
        $commentIdArr = PlatformComment::select('platform_comment_id as comment_id')
            ->where(['platform_product_id' => $productId, 'is_delete' => 0])
            ->pluck('comment_id')->toArray();
        return $commentIdArr ? $commentIdArr : [];
    }

    /**
     * @param $productId
     * @return int
     * 评论——有价值评论  点赞总个数大于等于2个的所有评论id
     */
    public static function fetchCommentValueId($productId)
    {
        $commentIdArr = PlatformComment::select('platform_comment_id as comment_id')
            ->whereRaw('use_count+add_count>1')
            ->where(['platform_product_id' => $productId, 'is_delete' => 0])
            ->pluck('comment_id')->toArray();
        return $commentIdArr ? $commentIdArr : [];
    }

    /**
     * @param $commentId
     * @return array
     * 评论——根据评论id获取评论内容
     */
    public static function fetchCommentById($commentId, $pageSize, $pageNum)
    {
        //评论内容
        $query = PlatformComment::select('platform_comment_id', 'platform_id', 'content', 'result', 'use_count', 'add_count', 'is_delete', 'create_date', 'create_id', 'experience', 'rate', 'speed')
            ->where(['is_delete' => 0])
            ->whereIn('platform_comment_id', $commentId)
            ->orderBy('create_date', 'desc');

        $count = $query->count();
        $page = PageStrategy::getPage($count, $pageSize, $pageNum);
        $commentArr['list'] = $query->limit($page['limit'])->offset($page['offset'])->get()->toArray();
        $commentArr['pageCount'] = $page['pageCount'];

        return $commentArr ? $commentArr : [];
    }

    /**
     * @param $userId
     * @param $productId
     * 评论——获取用户评论内容
     */
    public static function fetchCommentDatas($userId, $productId)
    {
        $comment = PlatformComment::select(['content', 'loan_money', 'speed', 'rate', 'experience', 'result'])
            ->where(['create_id' => $userId, 'platform_product_id' => $productId])
            ->where(['is_delete' => 0])
            ->orderBy('update_date', 'desc')
            ->limit(1)->first();
        return $comment ? $comment->toArray() : [];
    }

    /**
     * @param $commentArr
     * @param $userId
     * @param $productId
     * 评论——修改评论内容
     */
    public static function updateCommentDatas($commentArr, $userId, $productId)
    {
        $comment = PlatformComment::where(['create_id' => $userId, 'platform_product_id' => $productId])
            ->where(['is_delete' => 0])
            ->orderBy('create_date', 'desc')
            ->first();

        if (empty($comment)) {
            $comment = new PlatformComment();
            $comment->create_date = date('Y-m-d H:i:s', time());
            $comment->create_id = $userId;
        }
        $comment->speed = $commentArr['speed'];
        $comment->rate = $commentArr['rate'];
        $comment->experience = $commentArr['experience'];
        $comment->loan_money = $commentArr['loan_money'];
        $comment->platform_id = $commentArr['platform_id'];
        $comment->platform_product_id = $productId;
        $comment->content = $commentArr['content'];
        $comment->result = $commentArr['result'];
        $comment->update_date = date('Y-m-d H:i:s', time());
        $comment->update_id = $userId;
        return $comment->save();
    }

    /**
     * @param $userId
     * @param $commentId
     * 评论 —— 判断是否点赞
     */
    public static function getCommentUserful($userId, $commentId, $status)
    {
        $is_comment = CommentUseful::where(['user_id' => $userId, 'comment_id' => $commentId, 'status' => $status])
            ->first();
        return $is_comment ? $is_comment->toArray() : [];
    }

    /**
     * @param $userId
     * @param $commentId
     * 评论——点赞
     */
    public static function updateCommentUserful($userId, $commentId)
    {
        $useful = CommentUseful::where(['user_id' => $userId, 'comment_id' => $commentId, 'status' => 1])
            ->first();
        if (empty($useful)) {
            $useful = new CommentUseful();
            $useful->created_at = date('Y-m-d H:i:s', time());
            $useful->created_ip = Utils::ipAddress();
        }
        $useful->user_id = $userId;
        $useful->comment_id = $commentId;
        $useful->status = 0;
        $useful->updated_at = date('Y-m-d H:i:s', time());
        $useful->updated_ip = Utils::ipAddress();
        return $useful->save();
    }

    /**
     * @param $commentId
     * 评论——更新sd_platform_comment 中的use_count 加1
     */
    public static function increUsecommentById($commentId)
    {
        $platformComment = PlatformComment::where(['platform_comment_id' => $commentId])->increment('use_count');
        return $platformComment;
    }

    /**
     * @param $userId
     * @param $commentId
     * @return bool
     * 评论——取消点赞
     */
    public static function deleteCommentUserful($userId, $commentId, $status)
    {
        $useful = CommentUseful::where(['user_id' => $userId, 'comment_id' => $commentId, 'status' => 0])->update([
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
        ]);
        return $useful;
    }

    /**
     * @param $commentId
     * @return mixed
     * 评论——更新sd_platform_comment 中的use_count 减1
     */
    public static function decreUsecommentById($commentId)
    {
        $platformComment = PlatformComment::where(['platform_comment_id' => $commentId])->decrement('use_count');
        return $platformComment;
    }

    /**
     * 判断用户是否填写过评价——用于判断添加评论 、修改评论 ios
     * @param array $data
     */
    public static function fetchIsComment($data = [])
    {
        $userId = $data['userId'];
        $productId = $data['productId'];
        //判断用户是否填写过评价——用于判断添加评论 、修改评论
        $commentObj = PlatformComment::where(['create_id' => $userId, 'platform_product_id' => $productId, 'is_delete' => 0])
            ->orderBy('update_date', 'desc')
            ->limit(1)->first();
        return $commentObj ? 1 : 0;
    }

    /**
     * @param $productId
     * @return int
     * 所有评论总个数
     */
    public static function commentAllCount($productId)
    {
        $count = PlatformComment::select(['platform_comment_id'])
            ->where(['platform_product_id' => $productId, 'is_delete' => 0])
            ->count();
        return $count ? $count : 0;
    }

    /**
     * @param $productId
     * @return int
     * 所有评论总个数
     */
    public static function commentCounts($productId)
    {
        // is_delete 0存在 result 0 无状态
        $count = PlatformComment::select(['platform_comment_id'])
            ->where(['platform_product_id' => $productId, 'is_delete' => 0])
            ->where('result', '<>', 0)
            ->count();
        return $count ? $count : 0;
    }

    /**
     * @param $productId
     * @return mixed
     * 有价值评论的总个数
     */
    public static function commentValueCount($productId)
    {
        $count = PlatformComment::select()
            ->whereRaw('use_count+add_count>1')
            ->where(['platform_product_id' => $productId, 'is_delete' => 0])
            ->count();

        return $count ? $count : 0;
    }

    /**
     * @param $data
     * @param $productData
     * @param $userId
     * @return mixed
     * 评论——评论列表用户是否点赞
     * is_useful 1点击过 0未点击
     */
    public static function clickUseful($commentContent, $userId)
    {
        foreach ($commentContent as $pk => $pv) {
            $useful = CommentUseful::select('sd_useful_id')
                ->where(['comment_id' => $pv['platform_comment_id'], 'user_id' => $userId, 'status' => 0])
                ->get()->toArray();
            if ($useful) {
                $commentContent[$pk]['is_useful'] = 1;
            } else {
                $commentContent[$pk]['is_useful'] = 0;
            }
        }
        return $commentContent ? $commentContent : [];
    }

    /**
     * @param $comment
     * @param $userId
     * @return array
     * 评论——评论列表用户是否点赞  单个评论
     * is_useful 1点击过 0未点击
     */
    public static function clickUsefulSingle($comment, $userId)
    {
        $useful = CommentUseful::select('sd_useful_id')
            ->where(['comment_id' => $comment['platform_comment_id'], 'user_id' => $userId, 'status' => 0])
            ->get()->toArray();
        if ($useful) {
            $comment['is_useful'] = 1;
        } else {
            $comment['is_useful'] = 0;
        }

        return $comment ? $comment : [];
    }

    /**
     * @param $productId
     * @param $result
     * @return string
     * 借款状态个数统计
     */
    public static function fetchResultCounts($productId, $result)
    {
        //is_delete 0存在
        $resultCounts = PlatformComment::select('platform_comment_id')
            ->where(['platform_product_id' => $productId, 'result' => $result])
            ->where(['is_delete' => 0])
            ->count();
        return $resultCounts ? $resultCounts : 0;
    }

    /**
     * @param $productId
     * 最热评论id  点赞数和评论数之和 大于 2 显示
     */
    public static function fetchCommentHotsIds($productId, $resultId)
    {
        $commentIds = PlatformComment::select('platform_comment_id as comment_id')
            ->from('sd_platform_comment as comment')
            ->join('sd_data_platform_comment_count as data', 'data.comment_id', '=', 'comment.platform_comment_id')
            ->whereRaw('comment.use_count+comment.add_count+data.reply_count>2')
            ->where(['platform_product_id' => $productId, 'is_delete' => 0])
            ->when($resultId, function ($query) use ($resultId) {
                return $query->where('comment.result', $resultId);
            })
            ->pluck('comment_id')->toArray();

        return $commentIds ? $commentIds : [];
    }

    /**
     * @param $productId
     * @param $resultId
     * @return array
     * 所有评论id
     */
    public static function fetchCommentsIds($productId, $resultId)
    {
        $commentIds = PlatformComment::select('platform_comment_id as comment_id')
            ->from('sd_platform_comment as comment')
            ->where(['platform_product_id' => $productId, 'is_delete' => 0])
            ->when($resultId, function ($query) use ($resultId) {
                return $query->where('comment.result', $resultId);
            })
            ->pluck('comment_id')->toArray();

        return $commentIds ? $commentIds : [];
    }

    /**
     * 置顶产品评论id 限制两条
     * @param array $productId
     * @return array
     *
     */
    public static function fetchCommentTopIds($productId = [])
    {
        $commentIds = PlatformComment::select('platform_comment_id as comment_id')
            ->from('sd_platform_comment as comment')
            ->where(['platform_product_id' => $productId, 'is_delete' => 0])
            ->orderBy('is_top', 'desc')
            ->orderBy('comment_id', 'desc')
            ->limit(2)
            ->pluck('comment_id')->toArray();

        return $commentIds ? $commentIds : [];
    }

    /**
     * @param $commentIds
     * @param $pageSize
     * @param $pageNum
     * @return array
     * 所有评论
     */
    public static function fetchCommentsById($commentIds, $pageSize, $pageNum)
    {
        $query = PlatformComment::select('platform_comment_id', 'platform_id', 'content', 'result', 'use_count', 'add_count', 'create_date', 'create_id', 'experience', 'rate', 'speed', 'satisfaction')
            ->where(['is_delete' => 0])
            ->whereIn('platform_comment_id', $commentIds)
            ->orderBy('create_date', 'desc')
            ->with('dataPlatformCommentCount');

        $count = $query->count();
        $page = PageStrategy::getPage($count, $pageSize, $pageNum);
        $commentArr['list'] = $query->limit($page['limit'])->offset($page['offset'])->get()->toArray();
        $commentArr['pageCount'] = $page['pageCount'];

        return $commentArr ? $commentArr : [];
    }

    /**
     * @param $commentId
     * @return
     * 单个评论
     */
    public static function fetchCommentHotById($commentId)
    {
        $commentHot = PlatformComment::select('platform_comment_id', 'platform_id', 'content', 'result', 'use_count', 'add_count', 'create_date', 'create_id', 'experience', 'rate', 'speed', 'satisfaction')
            ->from('sd_platform_comment as comment')
            ->with('dataPlatformCommentCount')
            ->where(['comment.is_delete' => 0])
            ->where(['comment.platform_comment_id' => $commentId])
            ->orderBy('comment.create_date', 'desc')
            ->first();

        return $commentHot ? $commentHot->toArray() : [];
    }

    /**
     * @param $userId
     * @param $productId
     * @return array
     * 获取用户评论内容
     */
    public static function fetchCommentsBefore($userId, $productId)
    {
        $comment = PlatformComment::select(['platform_comment_id', 'content', 'loan_money', 'experience', 'result', 'satisfaction'])
            ->where(['create_id' => $userId, 'platform_product_id' => $productId])
            ->where(['is_delete' => 0])
            ->orderBy('update_date', 'desc')
            ->limit(1)->first();
        return $comment ? $comment->toArray() : [];
    }

    /**
     * @param $commentId
     * @param $key
     * @return string
     * 获取评论的审批时间
     */
    public static function fetchApplyTime($commentId, $key)
    {
        $applyTime = PlatformCommentProperty::select(['value'])
            ->where(['key' => $key, 'status' => 0, 'comment_id' => $commentId])
            ->first();
        return $applyTime ? $applyTime->value : '';
    }

    /**
     * @param $userId
     * @param $productId
     * @return string
     * 获取评论id
     */
    public static function fetchCommentId($userId, $productId)
    {
        $commentId = PlatformComment::select(['platform_comment_id'])
            ->where(['create_id' => $userId, 'platform_product_id' => $productId])
            ->where(['is_delete' => 0])
            ->orderBy('update_date', 'desc')
            ->limit(1)->first();
        return $commentId ? $commentId->platform_comment_id : '';
    }

    /**
     * @param $commentArr
     * @param $userId
     * @param $productId
     * @return mixed
     * 创建或修改评论内容
     */
    public static function createOrUpdateComment($commentArr, $userId, $productId)
    {
        $comment = PlatformComment::select()
            ->where(['create_id' => $userId, 'platform_product_id' => $productId, 'is_delete' => 0])
            ->orderBy('update_date', 'desc')
            ->first();
        if (empty($comment)) {
            $comment = new PlatformComment();
            $comment->create_date = date('Y-m-d H:i:s', time());
            $comment->create_id = $userId;
        }

        $comment->speed = $commentArr['speed'];
        $comment->rate = $commentArr['rate'];
        //新版本2.7.5.1评论使用新字段satisfaction
        $comment->satisfaction = $commentArr['experience'];
        $comment->loan_money = $commentArr['loan_money'];
        $comment->platform_id = $commentArr['platform_id'];
        $comment->platform_product_id = $productId;
        $comment->content = $commentArr['content'];
        $comment->result = $commentArr['result'];
        $comment->update_date = date('Y-m-d H:i:s', time());
        $comment->update_id = $userId;

        return $comment->save();
    }

    /**
     * @param $commentId
     * @param string $key
     * @param array $data
     * @return mixed
     * 修改评论特性内容
     */
    public static function createOrUpdateCommentProperty($commentId, $key = '', $data = [])
    {
        $commentProperty = PlatformCommentProperty::updateOrCreate(['comment_id' => $commentId, 'key' => $key], [
            'comment_id' => $commentId,
            'key' => $key,
            'value' => isset($data['applyTime']) ? $data['applyTime'] : 0,
            'status' => 0,
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
        ]);

        return $commentProperty;
    }

    /**
     * @param $productId
     * @param $resultId
     * @return array
     * 近30天被点赞评论id
     */
    public static function fetchCommentUsefulIdsByUseful($productId, $resultId)
    {
        $commentUsefulIds = PlatformComment::select('platform_comment_id as comment_id')
            ->from('sd_platform_comment as comment')
            ->join('sd_comment_useful as useful', 'useful.comment_id', '=', 'comment.platform_comment_id')
            ->where(['comment.platform_product_id' => $productId, 'comment.is_delete' => 0, 'useful.status' => 0])
            ->whereBetween('useful.updated_at', [date('Y-m-d H:i:s', strtotime('-30 days')), date('Y-m-d H:i:s')])
//            ->whereRaw('useful.updated_at BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()')
            ->when($resultId, function ($query) use ($resultId) {
                return $query->where('comment.result', $resultId);
            })
            ->pluck('useful.comment_id')->toArray();
        return $commentUsefulIds ? $commentUsefulIds : [];
    }

    /**
     * @param $loanMoney
     * @param $money
     * @return bool
     * 判断评论的借款范围
     */
    public static function isCheckRange($loanMoney, $money)
    {
        if (isset($loanMoney) && !empty($loanMoney)) {
            if ($loanMoney < $money['loan_min'] || $loanMoney > $money['loan_max']) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $params
     * @return mixed
     * 判断申请记录是否有评论
     */
    public static function fetchHistorysIsComment($params)
    {
        foreach ($params as $key => $val) {
            $comment = PlatformComment::select()
                ->where(['create_id' => $val['user_id'], 'platform_product_id' => $val['platform_product_id'], 'is_delete' => 0])
                ->orderBy('update_date', 'desc')
                ->first();
            if ($comment) {
                $params[$key]['is_comment'] = 1;
            } else {
                $params[$key]['is_comment'] = 0;
            }
        }

        return $params;
    }

    /**
     * @param array $params
     * @return int
     * @status 0点赞，1未点赞
     * @is_delete 状态【0未删除，1删除】
     * 回复点赞数量
     */
    public static function fetchReplyUsefulCountById($params = [])
    {
        $count = CommentReplyUseful::where(['reply_id' => $params['id'], 'status' => 0, 'is_delete' => 0])
            ->count();

        return $count ? $count : 0;
    }

    /**
     * 产品详情展示评论数据
     * @is_top 是否置顶, 1是,0非
     * @param array $params
     * @return array
     */
    public static function fetchDetailCommentsById($params = [])
    {
        $query = PlatformComment::select('platform_comment_id', 'platform_id', 'content', 'result', 'use_count', 'add_count', 'create_date', 'create_id', 'experience', 'rate', 'speed', 'satisfaction')
            ->where(['is_delete' => 0])
            ->whereIn('platform_comment_id', $params['commentIds'])
            //->where(['is_top' => 1])
            ->orderBy('is_top', 'desc')
            ->orderBy('create_date', 'desc');

        $count = $query->count();
        $page = PageStrategy::getPage($count, $params['pageSize'], $params['pageNum']);
        $commentArr['list'] = $query->limit($page['limit'])->offset($page['offset'])->get()->toArray();
        $commentArr['pageCount'] = $page['pageCount'];

        return $commentArr ? $commentArr : [];
    }
}
