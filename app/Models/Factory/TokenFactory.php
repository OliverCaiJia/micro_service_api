<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\UserTokens;
use App\Helpers\Generator\TokenGenerator;
use App\Helpers\Utils;

class TokenFactory extends AbsModelFactory
{
    /**
     *  登出更新Token
     * @param type $userId
     * @param type $type
     * @return type
     */
    public static function logoutTokenExpired($params)
    {
        /*
         *  Type 1 iOS
         *  Type 2 Android
         *  Type 3 H5/Wechat 
         */
        return UserTokens::whereColumn([
                ['type', '=', $params['type']],
                ['user_id', '=', $params['userId']],
                ['expired_time', '>', date('Y-m-d H:i:s', time())]
            ])->update(['expired_time' => date('Y-m-d H:i:s', time())]);
    }

    /**
     * 登录获取Token
     * @param type $userId
     * @param type $type
     * @return type
     */
    public static function fetchOrCreateUserToken($params)
    {
        $type = isset($params['type']) ? $params['type'] : 3;
        $userId = isset($params['userId']) ? $params['userId'] : 0;
        $token = isset($params['token']) ? $params['token'] : TokenGenerator::generateToken();
        $expired_time = isset($params['expired_time']) ? $params['expired_time'] : date('Y-m-d', strtotime('+1 month'));

        $userToken = UserTokens::firstOrCreate([
                ['type', '=', $type],
                ['user_id', '=', $userId]
        ], [
            'user_id' => $userId,
            'type' => $type,
            'access_token' => $token,
            'expired_time' => $expired_time,
            'created_at' => date('Y-m-d H:i:s', time()),
            'created_ip' => Utils::ipAddress(),
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress()
        ]);
        $now = date('Y-m-d H:i:s', time());
        if ($userToken->expired_time < $now)
        {
            $userToken->access_token = TokenGenerator::generateToken();
            $userToken->expired_time = date('Y-m-d', strtotime('+1 month'));
            $userToken->updated_at = date('Y-m-d H:i:s', time());
            $userToken->updated_ip = Utils::ipAddress();
            $userToken->save();
        }
        return $userToken->access_token;
    }
}
