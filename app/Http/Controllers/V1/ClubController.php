<?php

namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Club\Login\DoLoginHandler;
use App\Models\Chain\Club\Register\DoRegisterHandler;
use App\Models\Factory\ClubFactory;
use App\Models\Factory\UserFactory;
use App\Strategies\UserStrategy;
use Illuminate\Http\Request;

/**
 * Class ClubController
 * @package App\Http\Controllers\V1
 * 论坛
 */
class ClubController extends Controller
{
    /**
     * @param Request $request
     * 论坛登录
     */
    public function clubBind(Request $request)
    {
        //速贷之家用户id
        $userId = $request->user()->sd_user_id;
        $referer = $request->input('referer', '');

        //速贷之家联合论坛登录所需用户信息
        $userinfo = UserFactory::fetchUserinfoToClubByUserId($userId);

        //查表 sd_user_club 判断是否存在论坛用户 并获取论坛用户的登录信息
        $userClub = ClubFactory::fetchUserClub($userId);
        //性别
        $userinfo['sex'] = UserFactory::fetchIntSex($userId);
        $userinfo['referer'] = $referer;
        $userinfo['username'] = $userinfo['username'] . '_' . UserStrategy::getRandChar(2, 'NUMBER');

        if (empty($userClub)) {
            //调论坛注册的责任链
            $registerData = new DoRegisterHandler($userinfo);
            $re = $registerData->handleRequest();

        } else {
            //调论坛登录的责任链
            $userClub['mobile'] = !empty($userinfo['mobile']) ? $userinfo['mobile'] : '';
            $userClub['referer'] = $referer;

            $loginReferer = new DoLoginHandler($userClub);
            $re = $loginReferer->handleRequest();
        }

        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }
        return RestResponseFactory::ok($re);

    }
}