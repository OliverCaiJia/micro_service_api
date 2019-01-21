<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsBaseModel;
use App\Models\Orm\UserClub;

/**
 * Class ClubFactory
 * @package App\Models\Factory
 * 论坛
 */
class ClubFactory extends AbsBaseModel
{

    /**
     * @param $userId
     * @return array
     * 获取论坛用户的登录信息
     */
    public static function fetchUserClub($userId)
    {
        $userClub = UserClub::select(['club_user_id', 'user_id', 'club_username', 'club_password', 'session_token', 'uia'])
            ->where(['status' => 1, 'user_id' => $userId])
            ->first();
        return $userClub ? $userClub->toArray() : [];
    }

    /**
     * @param $registerData
     * @param $userId
     * 注册成功 添加关联论坛用户信息
     */
    public static function createUserClub($registerData)
    {
        $userClub = UserClub::firstOrCreate(['user_id' => $registerData['user_id']], [
                'user_id' => $registerData['user_id'],
                'club_user_id' => $registerData['club_user_id'],
                'club_username' => $registerData['username'],
                'club_password' => $registerData['password'],
                'club_groupid' => 0,
                'session_token' => $registerData['session_token'],
                'uia' => $registerData['uia'],
                'status' => 1,
                'last_login_time' => date('Y-m-d H:i:s', $registerData['regtime']),
                'last_login_ip' => Utils::ipAddress(),
                'created_at' => date('Y-m-d H:i:s', $registerData['regtime']),
                'created_ip' => Utils::ipAddress(),
                'updated_at' => date('Y-m-d H:i:s', $registerData['regtime']),
                'updated_ip' => Utils::ipAddress()
        ]);

        return $userClub;
    }

    /**
     * 
     * @param type $data
     * @return type
     */
    public static function sucessUserClub($data)
    {
        return UserClub::where('user_id', '=', $data['user_id'])->update(['status' => 1]);
    }

    /**
     * @param $userId
     * 已注册 更新时间
     */
    public static function updateLoginTime($params = [])
    {
        $userClub = UserClub::select()
            ->where(['user_id' => $params['user_id']])
            ->first();

        $userClub->last_login_time = date('Y-m-d H:i:s', $params['logintime']);
        $userClub->last_login_ip = Utils::ipAddress();
        $userClub->updated_at = date('Y-m-d H:i:s', $params['logintime']);
        $userClub->updated_ip = Utils::ipAddress();
        $userClub->uia = $params['uia'];
        $userClub->session_token = $params['session_token'];

        return $userClub->save();
    }

    /**
     * @param array $params
     * @return mixedx
     * 修改论坛用户密码
     */
    public static function updateUserClubPwd($params = [])
    {
        $res = UserClub::where(['user_id' => $params['user_id']])
            ->update([
                'club_password' => $params['new_password'],
                'last_login_time' => date('Y-m-d H:i:s', time()),
                'last_login_ip' => Utils::ipAddress(),
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress()
            ]);
        return $res;
    }

}
