<?php

namespace App\Models\Factory;

use App\Constants\CreditConstant;
use App\Constants\InviteConstant;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserCredit;
use App\Models\Orm\UserCreditLog;
use App\Models\Orm\UserInvite;
use App\Models\Orm\UserInviteCode;
use App\Models\Orm\UserInviteLog;
use App\Strategies\CreditStrategy;
use App\Strategies\InviteStrategy;
use App\Strategies\PageStrategy;

/**
 * 邀请工厂
 */
class InviteFactory extends AbsModelFactory
{

    /**
     * 获取邀请好友的个数
     * @param $user_id
     * @return int
     */
    public static function fetchUserInvitations($user_id)
    {
        $invite = UserInvite::select(['invite_num'])->where(['user_id' => $user_id])->first();
        return $invite ? $invite->invite_num : 0;
    }

    /**
     * 获取邀请人记录表数据
     * @uid  邀请人id
     * @param $uid
     */
    public static function getInvitedUsers($user_id)
    {
        $invite = UserInvite::where('user_id', '=', $user_id)->first();
        return $invite;
    }

    /**
     * 给邀请表中添加数据（废弃|邀请积分在积分表直接记录）
     * @param $uid
     */
    public static function insertInvite($uid)
    {
        $invite = new UserInvite();
        $invite->user_id = $uid;
        if (isset($invite->invite_num) && $invite->invite_num <= 3) {
            if ($invite->invite_num == 0) {
                $invite->score += 150;
            } elseif ($invite->invite_num == 1) {
                $invite->score += 200;
            } elseif ($invite->invite_num == 2) {
                $invite->score += 250;
            }
        }
        $invite->invite_num += 1;
        return $invite->save();
    }

    /**
     * @param $userId
     * @return array
     * 查询userId邀请码
     */
    public static function getCodeData($userId)
    {
        $inviteCode = UserInviteCode::select(['code', 'expired_at'])
            ->where(['user_id' => $userId])
            ->first();
        return $inviteCode ? $inviteCode->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 验证并生成邀请码
     */
    public static function fetchInviteCode($userId)
    {
        $inviteCode = UserInviteCode::firstOrCreate(['user_id' => $userId], [
            'user_id' => $userId,
            'code' => InviteStrategy::createCode(),
            'status' => 0,
            'created_user_id' => $userId,
            'expired_at' => '2116-01-01 00:00:00',
            'created_at' => date('Y-m-d H:i:s', time()),
            'created_ip' => Utils::ipAddress(),
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_user_id' => $userId,
            'updated_ip' => Utils::ipAddress(),
        ]);
        $now = date('Y-m-d H:i:s', time());
        if ($inviteCode->expired_at < $now) {
            $inviteCode->code = InviteStrategy::createCode();
            $inviteCode->expired_at = '2116-01-01 00:00:00';
            $inviteCode->updated_at = date('Y-m-d H:i:s', time());
            $inviteCode->updated_user_id = $userId;
            $inviteCode->updated_ip = Utils::ipAddress();
            $inviteCode->save();
        }
        return $inviteCode->code ? $inviteCode->code : '';
    }

    /**
     * @param $userId
     * @return array
     * 查询userId邀请码 object
     */
    public static function getInviteCode($userId)
    {
        $inviteCode = UserInviteCode::select(['code'])
            ->where(['user_id' => $userId])
            ->where('expired_at', '<=', date('Y-m-d H:i:s', time()))
            ->first();
        return $inviteCode ? $inviteCode->code : 0;
    }

    /**
     * @param $data
     * userId用户邀请流水
     */
    public static function fetchInviteLogData($data, $userId)
    {
        $pageSize = isset($data['pageSize']) ? $data['pageSize'] : 1;
        $pageNum = isset($data['pageNum']) ? $data['pageNum'] : 3;

        $invoteObj = UserInviteLog::where(['user_id' => $userId])
            ->select(['mobile', 'status']);
        $count = $invoteObj->count();
        //分页
        $page = PageStrategy::getPage($count, $pageSize, $pageNum);
        $invoteArr = $invoteObj->limit($page['limit'])->offset($page['offset'])->orderBy('created_at', 'desc')->get();
        $invoteLists['list'] = $invoteArr ? $invoteArr->toArray() : [];
        $invoteLists['pageCount'] = $page['pageCount'];
        return $invoteLists;
    }

    /**
     * @param $userId
     * @param $data
     * 短信邀请流水表查询
     */
    public static function fetchInviteLog($data)
    {
        $inviteLog = UserInviteLog::where(['mobile' => $data['mobile']])
            ->first();
        return $inviteLog ? $inviteLog->toArray() : [];
    }

    /**
     * 根据邀请码获得邀请人id
     * @param $code
     */
    public static function fetchInviteUserIdByCode($code)
    {
        $user_invite = UserInviteCode::select('user_id')->where('code', '=', $code)->first();
        return $user_invite ? $user_invite->user_id : '';
    }


    /**
     * 根据手机号和状态去邀请日志表中获得邀请人id
     * @param $code
     */
    public static function fetchInviteUserIdByMobileFromLog($mobile)
    {
        $user_invite_log = UserInviteLog::select('user_id', 'code')->where('mobile', '=', $mobile)->where('status', '=', 1)->orderBy('created_at', 'desc')->first();
        return $user_invite_log ? $user_invite_log->toArray() : [];
    }


    /**
     * 修改或者创建邀请日志记录表
     * @param $params
     * @return mixed
     */
    public static function updateOrCreateInviteLog($params)
    {
        $inviteObj = UserInviteLog::updateOrCreate(['mobile' => $params['mobile']], [
            'user_id' => intval($params['user_id']),
            'invite_user_id' => intval($params['invite_user_id']),
            'from' => $params['from'],
            'code' => $params['sd_invite_code'],
            'mobile' => $params['mobile'],
            'status' => $params['status'],
            'created_at' => date('Y-m-d H:i:s', time()),
            'created_user_id' => intval($params['user_id']),
            'created_ip' => Utils::ipAddress(),
        ]);
        $inviteObj->user_id = $params['user_id'];
        $inviteObj->invite_user_id = intval($params['invite_user_id']);
        $inviteObj->from = $params['from'];
        $inviteObj->code = $params['sd_invite_code'];
        $inviteObj->mobile = $params['mobile'];
        $inviteObj->status = $params['status'];
        $inviteObj->created_at = date('Y-m-d H:i:s', time());
        $inviteObj->created_user_id = intval($params['user_id']);
        $inviteObj->created_ip = Utils::ipAddress();
        return $inviteObj->save();
    }

    /**
     * 添加邀请流水表记录
     * @param $data
     * @return bool
     */
    public static function createInviteLog($invite)
    {
        #获取当前用户的用户id
        $user_id = $invite['userId'];
        #通过code码获取邀请人的id
        $invite_user_id = $invite['invite_user_id'];
        #判断如果邀请吗不存在，使用外层传过来的user_id
        $inviteObj = new UserInviteLog();
        $inviteObj->user_id = $user_id;
        $inviteObj->invite_user_id = intval($invite_user_id);
        $inviteObj->from = $invite['from'];
        $inviteObj->code = $invite['sd_invite_code'];
        $inviteObj->mobile = $invite['mobile'];
        $inviteObj->status = $invite['status'];
        $inviteObj->created_at = date('Y-m-d H:i:s', time());
        $inviteObj->created_user_id = $user_id;
        $inviteObj->created_ip = Utils::ipAddress();
        return $inviteObj->save();
    }


    /**
     *更新用户邀请表
     * @param $data
     * @return mixed
     */
    public static function updateInvite($params)
    {
        $inviteObj = UserInvite::firstOrCreate(['user_id' => $params['user_id']], [
            'user_id' => intval($params['user_id']),
            'invite_num' => 1,
            'update_ip' => Utils::ipAddress(),
        ]);
        $inviteObj->increment('invite_num', 1);
        return $inviteObj->save();
    }

    /**
     * 邀请好友获得积分规则
     * @param $user_id
     * @return int
     */
    public static function inviteScore($user_id)
    {
        $invite = UserInvite::where('user_id', '=', $user_id)->first();
        if ($invite->invite_num == 2) {
            $score = 300;
        } elseif ($invite->invite_num == 3) {
            $score = 450;
        } elseif ($invite->invite_num > 3) {
            $score = 0;
        } else {
            $score = 250;
        }
        return $score ? $score : 0;
    }

    public static function updateInviteLogStatus($mobile, $user_id)
    {
        return UserInviteLog::where('mobile', '=', $mobile)->update(['status' => 2, 'invite_user_id' => $user_id]);
    }

}
