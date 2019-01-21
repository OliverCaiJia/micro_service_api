<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\UserSign;

/**
 * Class UserSignFactory
 * @package App\Models\Factory
 * 签到工厂累
 */
class UserSignFactory extends AbsModelFactory
{
    /**
     * @param $userId
     * @return int
     * 用户签到状态
     */
    public static function fetchUserSignByUserId($userId)
    {
        $time = date('Y-m-d');
        $sign = UserSign::select(['id'])
            ->where(['user_id' => $userId, 'sign_at' => $time])
            ->first();

        return $sign ? 1 : 0;
    }
}