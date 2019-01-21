<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserSns;

/**
 * Class UserSignFactory
 * @package App\Models\Factory
 */
class UserSnsFactory extends AbsModelFactory
{
    /** 获取SNS用户登录信息
     * @param $userId
     * @return int
     */
    public static function fetchUserSns($userId)
    {
        $userSns = UserSns::where('user_id', $userId)->where('status', 1)->first();

        return $userSns ? $userSns->toArray() : [];
    }

    /** 保存sns关系表数据
     * @param array $params
     * @return bool
     */
    public static function createUserSns($params = [])
    {
        $sns = new UserSns();
        $sns->user_id = $params['user_id']; // 速贷用户id
        $sns->sns_user_id = $params['sns_user_id']; // sns用户id
        $sns->mobile = $params['mobile'];     //速贷用户手机号
        $sns->password = $params['password']; //速贷用户密码
        $sns->status = 1;                     // 标识当前用户是否可用
        $sns->created_at = date('Y-m-d H:i:s', time()); // 创建时间
        $sns->created_ip = Utils::ipAddress(); // 创建ip
        return $sns->save();
    }
}