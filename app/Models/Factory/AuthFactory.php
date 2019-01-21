<?php

namespace App\Models\Factory;

use App\Constants\UserConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DeliveryCount;
use App\Models\Orm\DeliveryLog;
use App\Models\Orm\UserAuth;
use App\Models\Orm\UserCertify;
use App\Models\Orm\UserIdentity;
use App\Models\Orm\UserProfile;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\UserStrategy;
use App\Helpers\Generator\TokenGenerator;
use App\Models\Factory\TokenFactory;
use Illuminate\Contracts\Logging\Log;

class AuthFactory extends AbsModelFactory
{

    /** 更新用户最后登录时间
     * @param $userId
     * @return mixed
     */
    public static function updateLoginTime($userId)
    {
        return UserAuth::where('sd_user_id', $userId)->update([
            'last_login_time' => date('Y-m-d H:i:s', time()),
            'last_login_ip' => Utils::ipAddress(),
        ]);
    }

    /**
     * @des 用户主表插入数据
     * @param $params
     */
    public static function createUser($params)
    {
        $username = !empty($params['username']) ? trim($params['username']) : 'sd' . UserStrategy::getRandChar(8, 'NUMBER');
        $indent = isset($params['indent']) ? intval($params['indent']) : 2;
        $password = isset($params['password']) ? $params['password'] : '';
        $version = isset($params['version']) ? intval($params['version']) : UserStrategy::version();

        $params['token'] = TokenGenerator::generateToken();
        $user = UserAuth::select('*')->where('mobile', '=', $params['mobile'])->first();
        if ($user) {
            $userAuth = UserAuth::updateOrCreate(['mobile' => $params['mobile']], [
                'mobile' => $params['mobile'],
                'password' => $password,
                'update_at' => date('Y-m-d H:i:s', time()),
                'update_ip' => Utils::ipAddress(),
                'last_login_time' => date('Y-m-d H:i:s', time()),
                'last_login_ip' => Utils::ipAddress(),
            ]);
        } else {
            $userAuth = UserAuth::firstOrCreate(['mobile' => $params['mobile']], [
                'username' => $username,
                'mobile' => $params['mobile'],
                'indent' => $indent,
                'activated' => isset($params['activated']) ? $params['activated'] : 0,
                'password' => $password,
                'accessToken' => $params['token'],
                'version' => $version ?: UserStrategy::version(),
                'update_at' => date('Y-m-d H:i:s', time()),
                'update_ip' => Utils::ipAddress(),
                'last_login_time' => date('Y-m-d H:i:s', time()),
                'last_login_ip' => Utils::ipAddress(),
            ]);
        }

        $user = UserAuth::select('create_at')->where('mobile', '=', $params['mobile'])->first();
        if (strtotime($user->create_at) < strtotime('2000-01-01 00:00:00')) {
            UserAuth::where('mobile', '=', $params['mobile'])->update([
                'create_at' => date('Y-m-d H:i:s', time()),
                'create_ip' => Utils::ipAddress(),
            ]);
        }
        $userAuth->where('mobile', '=', $params['mobile'])->whereRaw("LENGTH('accessToken') != 32")->update(['accessToken' => $params['token']]);
        // 更新Token表数据
        return $userAuth;
    }

    /**
     *
     * @dec 创建用户认证(sd_user_certify)
     * @param $params
     */
    public static function createUserCertify($params)
    {
        $userId = $params['sd_user_id'];
        $certify = UserCertify::updateOrCreate(['user_id' => $params['sd_user_id']], [
            'user_id' => intval($userId),
            'xuexin_website' => isset($params['xuexin_website']) ? $params['xuexin_website'] : 0, //学信网，1为无，2为可登陆
            'credit' => isset($params['credit']) ? $params['credit'] : 0, //信用卡；1为无，2为有
            'update_at' => date('Y-m-d H:i:s', time()),
            'update_id' => $userId,
            'create_id' => $userId,
        ]);
        $user = UserCertify::select('create_at')->where('user_id', '=', $params['sd_user_id'])->first();
        if (strtotime($user->create_at) < strtotime('2000-01-01 00:00:00')) {
            UserCertify::where('user_id', '=', $params['sd_user_id'])->update(['create_at' => date('Y-m-d H:i:s', time())]);
        }
        return $certify;
    }

    /**
     * @desc 创建用户身份信息
     * @param $params
     */
    public static function createUserIdentity($params)
    {
        $userId = $params['sd_user_id'];
        $identity = UserIdentity::updateOrCreate(['user_id' => $params['sd_user_id']], [
            'user_id' => intval($userId),
            'identity' => $params['indent'],
            'update_at' => date('Y-m-d H:i:s', time()),
            'update_ip' => Utils::ipAddress(),
            'update_id' => $userId,
            'create_id' => $userId,
        ]);
        $user = UserIdentity::select('create_at')->where('user_id', '=', $params['sd_user_id'])->first();
        if (strtotime($user->create_at) < strtotime('2000-01-01 00:00:00')) {
            UserIdentity::where('user_id', '=', $params['sd_user_id'])->update([
                'create_at' => date('Y-m-d H:i:s', time()),
                'create_ip' => Utils::ipAddress(),
            ]);
        }
        return $identity;
    }

    /**
     * 查出用户的accessToken
     */
    public static function getUserTokenById($userId)
    {
        return UserAuth::select('accessToken')->where('sd_user_id', '=', $userId)->first();
    }

    /**
     * 更新用户的accessToken
     */
    public static function updateUserTokenById($userId, $token)
    {
        return UserAuth::where('sd_user_id', '=', $userId)->update(['accessToken' => $token]);
    }

    /**
     * 登录或者注册的时候返回给客户端的信息
     * @param $userId
     * @return array
     */
    public static function fetchUserInfo($userId)
    {
        $res = UserFactory::fetchRealNameAndSex($userId);
        $userAuth = UserFactory::getUserById($userId);
        //获取用户头像
        $userinfo = UserFactory::fetchPhotoById($userId);
        //获取用户是否选择身份
        $isIdentity = UserinfoFactory::fetchIsIdentityById($userId);
        $info = [];
        $info['sd_user_id'] = $userAuth->sd_user_id;
        $info['mobile'] = $userAuth->mobile;
        $info['username'] = $userAuth->username;
        $info['indent'] = $userAuth->indent;
        $info['accessToken'] = $userAuth->accessToken;
        $info['activated'] = (strlen($userAuth->password) != 32) ? 0 : $userAuth->activated;
        $info['sex'] = $res['sex'];
        $info['realname'] = $res['realname'];
        //新添加
        $info['user_photo'] = isset($userinfo['user_photo']) ? QiniuService::getImgToPhoto($userinfo['user_photo']) : '';
        //判断是否选择身份 0没有选择，1已选择
        $info['is_identity'] = $isIdentity;
        $auth = $userAuth ? $userAuth->toArray() : [];
        $usernameData = UserStrategy::replaceUsernameSd($auth);
        $info['user_name'] = $usernameData['username'];
        //判断是否修改用户名 0添加，1修改
        $info['is_username'] = $usernameData['is_username'];
        return $info;
    }

    /**
     * 更新sd_user_auth中的indent 条件是indent为0的时候
     * @param $userId
     * @param $indent
     */
    public static function updateUserAuthIndent($userId, $indent = 2, $indent_update = false)
    {
        if ($indent_update) {
            return UserAuth::where('sd_user_id', '=', $userId)->where('indent', '=', 0)->update(['indent' => $indent]);
        } else {
            return UserAuth::where('sd_user_id', '=', $userId)->update(['indent' => $indent]);
        }
    }

    /**
     * 更新用户sd_user_identity表中的indent
     * @param $userId
     * @param  $indent
     */
    public static function updateUserIdentity($userId, $indent = 2, $indent_update = false)
    {
        if ($indent_update) {
            return UserIdentity::where('user_id', '=', $userId)->where('identity', '=', 0)->update(['identity' => $indent]);
        } else {
            return UserIdentity::where('user_id', '=', $userId)->update(['identity' => $indent]);
        }
    }


    /**
     * @param $userId
     */
    public static function getUserById($userId)
    {
        return UserAuth::select('*')->where('sd_user_id', '=', $userId)->first();
    }

    /**
     * @param $userId
     * @return mixed
     * 修改code标识status字段  1获取验证码成功  0验证码已验证通过
     */
    public static function updateUserAuthSMSCodeNotValidate($mobile)
    {
        return UserAuth::where(['mobile' => $mobile], ['status' => 0])->update(['status' => 1]);
    }

    /**
     * @param $userId
     * @return mixed
     * 修改code标识status字段  1获取验证码成功  0验证码已验证通过
     */
    public static function updateUserAuthSMSCodeIsValidate($mobile)
    {
        return UserAuth::where(['mobile' => $mobile], ['status' => 1])->update(['status' => 0]);
    }
}
