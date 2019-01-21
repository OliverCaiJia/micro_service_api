<?php

namespace App\Models\Factory;

use App\Helpers\DateUtils;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\CommentReply;
use App\Models\Orm\CommentReplyUseful;
use App\Models\Orm\DataCommentReplyCount;
use App\Models\Orm\DataPlatformCommentCount;
use App\Models\Orm\PlatformComment;
use App\Strategies\PageStrategy;
use App\Strategies\UserStrategy;
use Illuminate\Support\Facades\DB;

/**
 * 回复工厂
 * Class SmsFactory
 * @package App\Models\Factory
 */
class ReplyFactory extends AbsModelFactory
{
    /**
     * @param $data
     * @return bool
     * 创建回复
     */
    public static function createReply($data)
    {
        $reply = new CommentReply();
        $reply->comment_id = $data['commentId'];
        $reply->user_id = $data['userId'];
        $reply->content = $data['content'];
        $reply->created_ip = Utils::ipAddress();
        $reply->created_at = date('Y-m-d H:i:s', time());
        return $reply->save();
    }

    /**
     * @param $commentHots
     * @param $replyOffset
     * @return array
     * 回复数据
     */
    public static function fetchReplyCommentsById($commentHots, $replyOffset)
    {
        foreach ($commentHots as $key => $val) {
            $commentHots[$key]['replys'] = CommentReply::select('reply.content', 'auth.username')
                ->from('sd_platform_comment_reply as reply')
                ->join('sd_user_auth as auth', 'auth.sd_user_id', '=', 'reply.user_id')
                ->where(['comment_id' => $val['platform_comment_id']])
                ->where(['is_delete' => 0])
                ->orderBy('created_at', 'desc')
                ->limit($replyOffset)
                ->get()->toArray();
        }
        return $commentHots ? $commentHots : [];
    }

    /**
     * @param $commentId
     * @param $pageSize
     * @param $pageNum
     * @return array
     * 单个评论的回复
     */
    public static function fetchReplyComments($commentId, $pageSize, $pageNum)
    {
        $query = CommentReply::select('reply.content', 'auth.username')
            ->from('sd_platform_comment_reply as reply')
            ->join('sd_user_auth as auth', 'auth.sd_user_id', '=', 'reply.user_id')
            ->where(['comment_id' => $commentId])
            ->where(['is_delete' => 0])
            ->orderBy('created_at', 'desc');

        $count = $query->count();
        /**
         * 分页
         */
        $page = PageStrategy::getPage($count, $pageSize, $pageNum);
        $replyObj = $query->limit($page['limit'])->offset($page['offset'])->get();

        $replys['list'] = $replyObj ? $replyObj->toArray() : [];
        $replys['pageCount'] = $page['pageCount'];

        return $replys ? $replys : [];
    }

    /**
     * @param $commentId
     * @return mixed
     * 回复点击量
     */
    public static function updateReplyCount($commentId)
    {
        $replyCount = DataPlatformCommentCount::firstOrCreate(['comment_id' => $commentId], [
            'comment_id' => $commentId,
            'updated_ip' => Utils::ipAddress(),
            'updated_at' => date('Y-m-d H:i:s', time()),
            'reply_count' => 1,
        ]);
        $replyCount->increment('reply_count', 1);

        return $replyCount->save();
    }

    /**
     * @param $id
     * @return mixed
     * 多级回复  回复统计
     */
    public static function updateDateCommentReplyCount($data = [])
    {
        $replyCount = DataCommentReplyCount::firstOrCreate(['reply_id' => $data['parentReplyId']], [
            'reply_id' => $data['parentReplyId'],
            'updated_ip' => Utils::ipAddress(),
            'updated_at' => date('Y-m-d H:i:s', time()),
            'reply_count' => 1,
        ]);
        $replyCount->increment('reply_count', 1);

        return $replyCount->save();
    }

    /**
     * @param $commentHots
     * 回复的总条数
     */
    public static function fetchReplyCounts($commentHots)
    {
        foreach ($commentHots as $key => $val) {
            $commentHots[$key]['replys_count'] = CommentReply::where(['comment_id' => $val['platform_comment_id']])
                ->where(['is_delete' => 0])
                ->count();
        }

        return $commentHots ? $commentHots : [];
    }

    /**
     * @param $param
     * @return int
     * 单个评论回复总个数
     */
    public static function fetchReplyCountById($param)
    {
        $count = CommentReply::where(['comment_id' => $param])
            ->where(['is_delete' => 0])
            ->count();

        return $count ? $count : 0;
    }

    /**
     * @param $productId
     * @param $resultId
     * @return array
     * 近30天被回复的评论id
     */
    public static function fetchCommentReplyIdsByReply($productId, $resultId)
    {
        $commentReplyIds = PlatformComment::select('platform_comment_id as comment_id')
            ->from('sd_platform_comment as comment')
            ->join('sd_platform_comment_reply as reply', 'reply.comment_id', '=', 'comment.platform_comment_id')
            ->where(['comment.platform_product_id' => $productId, 'comment.is_delete' => 0, 'reply.is_delete' => 0])
            ->whereBetween('reply.created_at', [date('Y-m-d H:i:s', strtotime('-30 days')), date('Y-m-d H:i:s')])
//            ->whereRaw('reply.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()')
            ->when($resultId, function ($query) use ($resultId) {
                return $query->where('comment.result', $resultId);
            })
            ->pluck('useful.comment_id')->toArray();
        return $commentReplyIds ? $commentReplyIds : [];
    }

    /**
     * @param array $params
     * @param $replyOffset
     * @return array
     * 多级回复内容  限制3条
     */
    public static function fetchReplysById($params = [], $replyOffset)
    {
        foreach ($params as $key => $val) {
            $params[$key]['replys'] = CommentReply::select(['id', 'content', 'parent_reply_id', 'user_id'])
                ->where(['comment_id' => $val['platform_comment_id']])
                ->where(['is_delete' => 0])
                ->orderBy('created_at', 'desc')
                ->limit($replyOffset)
                ->get()->toArray();
        }
        return $params ? $params : [];
    }

    /**
     * @param $comments
     * @return array
     * 获取回复评论的用户信息
     */
    public static function fetchReplysUserinfo($comments)
    {
        foreach ($comments as $key => $val) {
            foreach ($val['replys'] as $k => $v) {
                $username = UserFactory::fetchUserNameAndMobile($v['user_id']);
                $username = UserStrategy::replaceUsernameSd($username);
                $comments[$key]['replys'][$k]['username'] = $username['username'];
                //父级用户
                $replyUser = ReplyFactory::fetchUserIdByReplyId($v['parent_reply_id']);
                if ($replyUser) {
                    $username = UserFactory::fetchUserNameAndMobile($replyUser['user_id']);
                    $username = UserStrategy::replaceUsernameSd($username);
                    $comments[$key]['replys'][$k]['parent']['username'] = $username['username'];
                    $comments[$key]['replys'][$k]['parent']['user_id'] = $replyUser['user_id'];
                    $comments[$key]['replys'][$k]['parent']['reply_id'] = $v['parent_reply_id'];
                    $comments[$key]['replys'][$k]['parent']['content'] = $replyUser['content'];
                } else {
                    $comments[$key]['replys'][$k]['parent'] = [];
                }
            }
        }
        return $comments ? $comments : [];
    }

    /**
     * @param $param
     * @return array
     * 回复者用户信息
     */
    public static function fetchUserIdByReplyId($param)
    {
        $replyUser = CommentReply::select(['id', 'user_id', 'content'])
            ->where(['id' => $param])
            //->where(['is_delete' => 0])
            ->first();

        return $replyUser ? $replyUser->toArray() : [];
    }

    /**
     * @param $data
     * @return bool
     * 带有回复聊天功能的 创建回复
     */
    public static function createReplys($data)
    {
        $reply = new CommentReply();
        $reply->parent_reply_id = $data['parentReplyId'];
        $reply->comment_id = $data['commentId'];
        $reply->user_id = $data['userId'];
        $reply->content = $data['content'];
        $reply->is_delete = 0;
        $reply->created_ip = Utils::ipAddress();
        $reply->created_at = date('Y-m-d H:i:s', time());
        return $reply->save();
    }

    /**
     * @param array $data
     * @return array
     * 多级回复  获取楼主的回复内容 并进行排序
     */
    public static function fetchReplyLandlords($data = [])
    {
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        $query = CommentReply::from('sd_platform_comment_reply as r')
            ->select(['r.id', 'r.content', 'r.parent_reply_id', 'r.user_id', 'c.reply_count', 'r.created_at'])
            ->addSelect(DB::raw('c.use_count+c.add_count+c.reply_count as total,c.use_count+c.add_count as use_count'))
            ->where(['r.comment_id' => $data['platform_comment_id']])
            ->where(['r.is_delete' => 0])
            ->leftjoin('sd_data_platform_comment_reply_count as c', 'r.id', '=', 'c.reply_id');

        //按热度进行排序
        $query->when($data['replyType'], function ($query) {
            $query->orderBy('total', 'desc');
        });
        //按时间进行排序
        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');


        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $replys = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $datas['list'] = $replys;
        $datas['pageCount'] = $countPage ? $countPage : 0;

        return $datas ? $datas : [];


    }

    /**
     * @param array $replys
     * @return array
     * 多级回复 用户信息
     */
    public static function fetchParentReplys($replys = [])
    {
        foreach ($replys as $key => $val) {
            $count = ReplyFactory::fetchCommentReplyCountById($val);
            $replys[$key]['total_count'] = $count ? bcadd(bcadd($count['use_count'], $count['add_count']), $count['reply_count']) : 0;
            $replys[$key]['use_count'] = $count ? bcadd($count['use_count'], $count['add_count']) : 0;
            $username = UserFactory::fetchUserNameAndMobile($val['user_id']);
            $username = UserStrategy::replaceUsernameSd($username);
            $replys[$key]['username'] = $username['username'];
            $replys[$key]['user_photo'] = UserFactory::fetchUserPhotoById($val['user_id']);
            $replys[$key]['reply_count'] = $count ? $count['reply_count'] : 0;
            //父级用户
            $replyUser = ReplyFactory::fetchUserIdByReplyId($val['parent_reply_id']);
            if ($replyUser) {
                $username = UserFactory::fetchUserNameAndMobile($replyUser['user_id']);
                $username = UserStrategy::replaceUsernameSd($username);
                $replys[$key]['parent']['username'] = $username['username'];
            } else {
                $replys[$key]['parent'] = [];
            }
        }
        return $replys;
    }

    /**
     * @param array $data
     * @return array
     * 多级回复  获取回复点赞、回复总量
     */
    public static function fetchCommentReplyCountById($data = [])
    {
        $count = DataCommentReplyCount::select()
            ->where(['reply_id' => $data['id']])
            ->first();

        return $count ? $count->toArray() : [];
    }

    /**
     * @param $id
     * @return array]
     * 根据回复点赞量与回复总量之和 筛选出回复id
     */
    public static function fetchReplyIdsByCount($id)
    {
        $ids = DataCommentReplyCount::select(['reply_id',
            DB::raw('use_count+add_count+reply_count as count'),
        ])
            ->where(['comment_id' => $id])
            ->orderBy('count', 'desc')
            ->pluck('reply_id')->toArray();

        return $ids ? $ids : [];
    }

    /**
     * @param array $params
     * @return array
     * 多级回复  判断是否对每个回复进行点赞
     */
    public static function clickUseful($params = [], $data = [])
    {
        foreach ($params as $key => $val) {
            $useful = CommentReplyUseful::select()
                ->where(['reply_id' => $val['id'], 'user_id' => $data['userId'], 'status' => 0, 'is_delete' => 0])
                ->first();
            if ($useful) {
                $params[$key]['is_useful'] = 1;
            } else {
                $params[$key]['is_useful'] = 0;
            }
        }

        return $params;
    }

    /**
     * @param array $data
     * @return mixed
     * 多级回复  对恢复点赞
     */
    public static function createOrupdateUsecount($data = [])
    {
        $query = CommentReplyUseful::firstOrCreate(['reply_id' => $data['replyId'], 'user_id' => $data['userId'], 'is_delete' => 0], [
            'reply_id' => $data['replyId'],
            'user_id' => $data['userId'],
            'status' => 0,
            'is_delete' => 0,
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
            'created_at' => date('Y-m-d H:i:s', time()),
            'created_ip' => Utils::ipAddress(),
        ]);

        $query->status = 0;
        $query->updated_at = date('Y-m-d H:i:s', time());
        $query->updated_ip = Utils::ipAddress();

        return $query->save();
    }

    /**
     * @param array $data
     * @return array
     * 判断该用户是否对该回复进行点赞
     */
    public static function checkIsReplyClickuseful($data = [])
    {
        $clickuseful = CommentReplyUseful::select()
            ->where(['reply_id' => $data['replyId'], 'user_id' => $data['userId'],
                'status' => $data['status'], 'is_delete' => 0])
            ->first();

        return $clickuseful ? $clickuseful->toArray() : [];
    }

    /**
     * @param array $data
     * @return mixed
     * 多级回复  回复点赞数量+1
     */
    public static function increReplyUsecountById($data = [])
    {
        $query = DataCommentReplyCount::firstOrCreate(['reply_id' => $data['replyId']], [
            'reply_id' => $data['replyId'],
            'use_count' => 1,
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
        ]);

        $query->increment('use_count', 1);

        return $query->save();
    }

    /**
     * @param array $data
     * @return mixed
     * 多级回复 回复取消点赞
     */
    public static function deleteUsecount($data = [])
    {
        $useful = CommentReplyUseful::where(['user_id' => $data['userId'], 'reply_id' => $data['replyId'], 'status' => 0])->update([
            'status' => 1,
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
        ]);
        return $useful;
    }

    /**
     * @param array $data
     * @return mixed
     * 多级回复 取消点赞 数量-1
     */
    public static function decreReplyUsecountById($data = [])
    {
        $query = DataCommentReplyCount::where(['reply_id' => $data['replyId']])->decrement('use_count');
        return $query;
    }
}