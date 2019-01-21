<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserConstant;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Models\Factory\AccountFactory;
use App\Models\Factory\AuthFactory;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\PhoneFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserSignFactory;
use App\Models\Orm\UserContacts;
use App\Services\Core\Store\Qiniu\Qiniu\QiniuUpload;
use App\Strategies\SmsStrategy;
use App\Strategies\UserStrategy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\RestResponseFactory;
use App\Models\Chain\Club\Password\DoPasswordHandler;

class UserController extends Controller
{

    function index(Request $request)
    {
        return RestResponseFactory::ok(null, 'OK');
    }


    /**修改身份&昵称
     * @param Request $request
     */
    public function updateUsernameAndIndent(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        $data['username'] = Utils::removeSpace($data['username']);
        $data['indent'] = intval($data['indent']);

        //修改sd_user_auth表中的username & indent
        $userAuth = UserFactory::updateUsernameAndIndent($data);
        //修改sd_user_identity中的身份
        $userIdentity = UserFactory::updateIdentity($data);

        if (empty($userAuth) || empty($userIdentity)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2000), 2000);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


    /**修改身份
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function updateIdentity(Request $request)
    {
        $data['indent'] = $request->input('identity');
        $data['userId'] = $request->user()->sd_user_id;
        //修改sd_user_auth表中的indent
        $userAuth = UserFactory::updateIndent($data);
        //修改sd_user_identity中的身份
        $userIdentity = UserFactory::updateIdentityAndIsIdentity($data);

        if (empty($userAuth) || empty($userIdentity)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2000), 2000);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return mixed
     * 验证短信是否正确接口
     */
    public function checkMobileCode(Request $request)
    {
        $data = $request->all();
        $checkCode = SmsStrategy::getCodeKeyAndSignKey($data['mobile'], $data['smsType']);
        $codeKey = $checkCode['codeKey'];
        $signKey = $checkCode['signKey'];
        //dd($checkCode);
        $checkRes = PhoneFactory::checkMobileAndCode($codeKey, $signKey, $data['code'], $data['sign']);
        if (empty($checkRes)) {
            //验证码不正确
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1204), 1204);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());

    }

    /**修改绑定手机号
     * @param Request $request
     */
    public function updateMobile(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1125), 1125);

        //修改手机号
        $codeKey = 'update_mobile_code_' . $data['mobile'];
        $signKey = 'update_mobile_random_' . $data['mobile'];

        $checkCode = PhoneFactory::checkMobileAndCode($codeKey, $signKey, $data['code'], $data['sign']);
        if (empty($checkCode)) {
            //验证码不正确
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1204), 1204);
        }
        //修改手机号
        $updateMobile = UserFactory::updateMobileById($userId, $data['mobile']);
        if (empty($updateMobile)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1122), 1122);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1125), 1125);

    }


    /**绑定手机号
     * @param Request $request
     */
    public function bindMobile(Request $request)
    {

    }


    /**
     *设置密码
     * @param Request $request
     */
    public function updatePwd(Request $request)
    {
        $user_id = $this->getUserId($request);
        $password = $request->input('password', '');
        $reset_token = $request->input('reset_token', false);

        //强制转换为bool型
        $reset_token = is_bool($reset_token) ? $reset_token : (bool)$reset_token;

        if ($password) {
            if ($reset_token === true) {
                $re = UserFactory::setUserPasswordAndToken($user_id, $password);
                $data['need_relogin'] = true;
                $message = '修改密码成功,请重新登陆。';
            } else {
                $re = UserFactory::setUserPassword($user_id, $password);
                $data['need_relogin'] = false;
                $message = '设置密码成功。';
            }

            if ($re) {
                UserFactory::setUserActivated($user_id);

                $datas['user_id'] = $user_id;
                $datas['new_password'] = $password;

//                $registerData = new DoPasswordHandler($datas);
//                $re = $registerData->handleRequest();
//                if (isset($re['error'])) {
//                    return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
//                }

                return RestResponseFactory::ok($data, $message);
            }
        }
        return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1109), 1109);
    }

    /**
     * @param Request $request
     * @return mixed
     * 忘记密码
     */
    public function forgetPwd(Request $request)
    {
        //接收手机号
        $data = $request->all();

        // 获取用户id
        $userId = UserFactory::getIdByMobile($data['mobile']);
        //更新登录时间
        AuthFactory::updateLoginTime($userId);
        // 重新生成token
        $re = UserFactory::setUserPasswordAndToken($userId, $data['password']);
        if (empty($re)) {
            //密码修改失败
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1109), 1109);
        }
        //返回用户信息
        $info = AuthFactory::fetchUserInfo($userId);

        return RestResponseFactory::ok($info);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 用户账户信息
     */
    public function fetchUserinfo(Request $request)
    {
        //通過Token獲取用戶ID
        $userId = $request->user()->sd_user_id;
        $data['userId'] = $userId;
        //账号余额
        $data['userAccount'] = AccountFactory::fetchBalance($userId);
        //账号积分
        $data['userScore'] = CreditFactory::fetchCredit($userId);
        //用户信息
        $data['userProfile'] = UserFactory::fetchRealNameAndSex($userId);
        //用户信息
        $data['userAuth'] = UserFactory::fetchUserNameAndMobile($userId);
        //用户头像
        $data['userinfo'] = UserFactory::fetchPhotoById($userId);
        //用户是否签到
        $data['user_sign'] = UserSignFactory::fetchUserSignByUserId($userId);

        $info = UserStrategy::fetchUserinfo($data);

        return RestResponseFactory::ok($info);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 修改用户头像
     */
    public function uploadPhoto(Request $request)
    {
        echo 111;die;
//        $data['userId'] = $request->user()->sd_user_id;
//        // 上传文件
//        $userPhoto = $request->file('userPhoto');
//        $photo = $request->file('file');
//
//        $file = isset($userPhoto) ? $userPhoto : $photo;
//
//        // 目标文件夹
//        $data['prefix'] = UserConstant::USER_PHOTO_PREFIX;
//        // 文件名称
//        $filename_path = QiniuUpload::uploadFile($file, $data);
//
//        if ($filename_path) {
//            $data['userPhoto'] = $filename_path;
//            //保存头像
//            $info = UserFactory::createOrUpdatePhoto($data);
//            if ($info) {
//                $datas['photo'] = UserFactory::fetchUserPhotoById($data['userId']);
//                return RestResponseFactory::ok($datas);
//            }
//        }
//
//        return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2000), 2000);
    }

}