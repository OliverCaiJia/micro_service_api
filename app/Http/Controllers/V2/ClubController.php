<?php

namespace App\Http\Controllers\V2;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\UserSnsFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserVipFactory;
use App\Models\Orm\UserDeviceLocation;
use App\Models\Orm\UserInfo;
use App\Models\Orm\UserProfile;
use App\Services\AppService;
use Illuminate\Http\Request;
use App\Models\Chain\Sns\Register\DoRegisterHandler;
use App\Helpers\Utils;

use App\Services\Core\SNS\Libs\SNSService;

/**
 * * OpenSNS联合登录接口
 * Class ClubController
 * @package App\Http\Controllers\V2
 */
class ClubController extends Controller
{
    /**联合登录
     * @param Request $request
     */
    public function clubBind(Request $request)
    {
        //速贷之家用户id
        $user = $request->user();
        $referer = $request->input('referer', '');

        $url = AppService::SNS_URL .'?s=/club/index/index';
        // 未登录状态直接返回链接
        if (empty($user))
        {
            $url = $url . '&_status=logout';
            return RestResponseFactory::ok(['redirect_url' => $url]);
        }

        // 登录状态生成免登陆链接
        $userId = $request->user()->sd_user_id;

        //查关联表 sd_user_opensns 判断是否存在用户 并获取SNS用户的登录信息
        $userSns = UserSnsFactory::fetchUserSns($userId);

        //速贷之家联合论坛登录所需用户信息
        $userinfo = UserFactory::fetchUserinfoToClubByUserId($userId);

        // 若关系表存在用户 则当前用户已注册 直接登录;若无用户,则先注册用户后登录.
        if (empty($userSns)) {
            //调SNS用户注册的责任链
            $registerHandler = new DoRegisterHandler($userinfo);
            $res = $registerHandler->handleRequest();

            // 注册失败
            if (isset($res['error']))
            {
                return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], 9106, $res['error']);
            }

            // 注册成功, 取SNS用户密码
            $userSns = UserSnsFactory::fetchUserSns($userId);
        }

        // 头像
        $photo = UserInfo::where('user_id', $userId)->value('user_photo');
        // 生日
        $birthday = UserProfile::where('user_id', $userId)->value('identity_card');
        // 性别
        $sex = UserProfile::where('user_id', $userId)->value('sex');
        // 城市
        $city = UserDeviceLocation::where('user_id', $userId)->orderBy('updated_at', 'desc')->value('user_city');
        // 会员类型id
        $vip_type_id = UserVipFactory::getVipTypeId();
        // 用户vip信息
        $vipinfo = UserVipFactory::getVIPInfo($userId,  $vip_type_id);

        $imageUrl = config('sudai.imageUrl');
        // 注册成功 || 已注册 => 直接登录
        $params = [
            'username' => $userSns['mobile'],
            'password' => $userSns['password'],
            'nickname' => $userinfo['username'], //用户昵称
            'photo'    => empty($photo) ? $imageUrl . 'production/20171221/privilege/20171221154747-410.png' : $imageUrl . $photo, // 头像
            'birthday' => empty($birthday) ? '0000-00-00' : substr($birthday, 6, 8),//生日
            'sex'      => ($sex == 0 or $sex == 1) ? $sex : 2,
            'city'     => $city,
            'is_vip'   => $vipinfo ? 1 : 0
        ];

        // 调用SNS登录接口
        $result =  SNSService::i()->login($params);

        if ($result['status'] == 1)
        {
            // 登录成功返回免登陆链接
            $cookie = Utils::urlsafe_base64encode($result['cookie']);

            // 跳转链接
            if (!empty($referer))
            {
                // 如果存在 logout
                if (strpos($referer, 'logout') !== false)
                {
                    $referer = AppService::SNS_URL .'?s=/club/index/index';
                }

                $referer = Utils::urlsafe_base64encode($referer);
                $url .= '&referer=' . $referer;
            }

            $url .= '&cookie=' . $cookie;
            return RestResponseFactory::ok(['redirect_url' => $url]);
        }
        elseif($result['status'] == 0)
        {
            // 登录失败, 返回错误信息
            return RestResponseFactory::ok(RestUtils::getStdObj(), $result['info'], 9107, RestUtils::getErrorMessage(9107));
        }
    }
}