<?php

namespace App\Models\Factory;

use App\Constants\UserConstant;
use App\Helpers\Generator\TokenGenerator;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserAgent;
use App\Models\Orm\UserBanks;
use App\Models\Orm\UserCertify;
use App\Models\Orm\UserIdentity;
use App\Models\Orm\UserInfo;
use App\Models\Orm\UserProfile;
use App\Models\Orm\UserAuth;
use App\Models\Orm\UserRealname;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\SexStrategy;
use Illuminate\Support\Facades\DB;

class UserFactory extends AbsModelFactory
{
    /** 从实名认证中根据身份证号获取用户id
     * @param int $idcard
     * @return string
     */
    public static function getUserIdByIdCard($idcard = 0)
    {
        if (!empty($idcard)) {
            $model = UserRealname::where('certificate_no', $idcard)->first();
            return $model ? $model->user_id : '';
        }
    }

    /** 根据身份证号获取用户真实姓名
     * @param int $idcard
     * @return string
     */
    public static function getRealNameByIdCard($idcard = 0)
    {
        if (!empty($idcard)) {
            $model = UserRealname::where('certificate_no', $idcard)->first();
            return $model ? $model->realname : '';
        }
    }

    /** 根据用户id获取用户
     * @param int $idcard
     * @return string
     */
    public static function getRealInfoByUserId($userId)
    {
        if (!empty($userId)) {
            $model = UserRealName::select(['user_id', 'certificate_no', 'realname'])->where('user_id', $userId)->first();
            return $model ? $model->toArray() : [];
        }
    }


    // 根据身份证获取用户id
    public static function fetchUserIdByIdcard($idcard = 0)
    {
        if (!empty($idcard)) {
            $userAuth = UserProfile::where('identity_card', '=', $idcard)->first();
            return $userAuth ? $userAuth->user_id : '';
        }

        return 0;
    }

    // 根据身份证获取真实姓名
    public static function fetchRealNameByIdcard($idcard = 0)
    {
        $userAuth = UserProfile::where('identity_card', '=', $idcard)->first();
        return $userAuth ? $userAuth->real_name : '';
    }

    /**
     * 返回姓名
     */
    public static function fetchRealName($userId)
    {
        $profile = UserProfile::select(['sex', 'real_name'])->where(['user_id' => $userId])->first();
        return (isset($profile->real_name) ? trim($profile->real_name) : "");
    }

    /**
     * 获取用户
     * @uid 邀请人id
     * @param $uid
     */
    public static function getUserByMobile($mobile)
    {
        return UserAuth::where('mobile', '=', $mobile)->first();
    }

    /**
     * 获取用户
     * @uid 邀请人id
     * @param $uid
     */
    public static function getUserById($userId)
    {
        return UserAuth::where('sd_user_id', '=', $userId)->first();
    }

    /**
     * 返回男女
     */
    public static function fetchSex($userId)
    {
        $profile = UserProfile::select(['sex', 'real_name'])->where(['user_id' => $userId])->first();
        return (isset($profile->sex) ? SexStrategy::intToStr($profile->sex) : "女");
    }

    /**
     * @param $userId
     * @return int
     * 返回男女整形
     */
    public static function fetchIntSex($userId)
    {
        $profile = UserProfile::select(['sex'])->where(['user_id' => $userId])->first();
        return $profile ? $profile->sex : 0;
    }

    /**
     * 返回男女+真是姓名
     */
    public static function fetchRealNameAndSex($userId)
    {
        $profile = UserProfile::select(['sex', 'real_name'])->where(['user_id' => $userId])->first();
        $user['sex'] = isset($profile->sex) ? SexStrategy::intToStr($profile->sex) : "女";
        $user['realname'] = isset($profile->real_name) ? trim($profile->real_name) : "";
        return $user;
    }

    /**
     * 返回用户name
     */
    public static function fetchUserName($userId)
    {
        $userAuth = UserAuth::select(['username', 'mobile'])->where(['sd_user_id' => $userId])->first();
        return isset($userAuth->username) ? trim($userAuth->username) : "";
    }

    /**
     * 返回用户手机号
     */
    public static function fetchMobile($userId)
    {
        $userAuth = UserAuth::select(['username', 'mobile'])->where(['sd_user_id' => $userId])->first();
        return (isset($userAuth->mobile) ? trim($userAuth->mobile) : "");
    }

    /**
     * @param $userId
     * @return array
     * 获取用户基本信息
     */
    public static function fetchUserById($userId)
    {
        $user = UserAuth::select(['username', 'mobile'])->where(['sd_user_id' => $userId])->first();
        $profile = UserProfile::select(['sex', 'real_name', 'identity_card'])->where(['user_id' => $userId])->first();
        $datas['user'] = $user ? $user->toArray() : [];
        $datas['profile'] = $profile ? $profile->toArray() : [];
        return $datas ? $datas : [];
    }

    /**
     * 返回用户+手机号
     */
    public static function fetchUserNameAndMobile($userId)
    {
        $userAuth = UserAuth::select(['username', 'mobile'])->where(['sd_user_id' => $userId])->first();
        $user['username'] = isset($userAuth->username) ? trim($userAuth->username) : "";
        $user['mobile'] = isset($userAuth->mobile) ? trim($userAuth->mobile) : "";
        return $user;
    }

    /**
     * 返回用户身份
     */
    public static function fetchUserIndent($userId)
    {
        $indent = UserAuth::select(['indent'])->where(['sd_user_id' => $userId])->first();
        return $indent ? $indent->indent : '';
    }

    /**
     * 返回用户身份
     */
    public static function fetchBankAndAccount($userId)
    {
        $userBank = UserBanks::select(['bank_id', 'account'])
            ->where(['user_id' => $userId, 'status' => 0, 'card_use' => 0])
            ->first();
        return $userBank ? $userBank->toArray() : [];
    }

    /**
     * 返回银行账户信息
     */
    public static function fetchAccount($userId)
    {
        $userBank = UserBanks::select(['account'])
            ->where(['user_id' => $userId, 'status' => 0, 'card_use' => 0])
            ->first();
        return $userBank ? $userBank->account : null;
    }

    /**
     * @param $userId
     * @return array
     * 用户信息——基础信息
     */
    public static function fetchUserProfile($userId)
    {
        $profileArr = UserProfile::select(['real_name', 'identity_card', 'address', 'address_type',
            'marriage', 'emergency_contact', 'emergency_contact_mobile', 'emergency_contact_relation'])
            ->where(['user_id' => $userId])->first();
        return $profileArr ? $profileArr->toArray() : [];
    }

    /**
     * @param $indent
     * @param $userId
     * @return array
     * 用户信息——审核资料信息
     */
    public static function fetchUserCertify($indent, $userId)
    {
        // 大学生
        if ($indent == 1) {
            $certifyArr = UserCertify::select(['xuexin_website', 'zhima_certify', 'people_bank_report',
                'taobao_certify', 'jingdong_certify', 'credit_money', 'provident_fund_money'])
                ->where(['user_id' => $userId])->first();
        } else {
            $certifyArr = UserCertify::select(['credit', 'zhima_certify', 'people_bank_report',
                'taobao_certify', 'jingdong_certify', 'credit_money', 'provident_fund_money'])
                ->where(['user_id' => $userId])->first();
        }
        return $certifyArr ? $certifyArr->toArray() : [];
    }

    /**
     * @param $indent
     * @param $userId
     * @return array
     * 用户信息——个人信息
     */
    public static function fetchUserIdentity($indent, $userId)
    {
        $identityArr = [];
        switch ($indent) {
            case 1:
                //大学生
                $identityArr = UserIdentity::select(['school_name', 'studies', 'graduate_long_year'])
                    ->where(['user_id' => $userId])->first();
                break;
            case 2:
                //工薪族
                $identityArr = UserIdentity::select(['certificate', 'company_name', 'company_nature',
                    'working_years', 'month_income', 'is_company_email', 'wage_water_proof'])
                    ->where(['user_id' => $userId])->first();
                break;
            case 3:
                //企业主
                $identityArr = UserIdentity::select(['certificate', 'company_name', 'company_nature',
                    'manage_time', 'month_income', 'business_license', 'is_bill'])
                    ->where(['user_id' => $userId])->first();
                break;
            case 4:
                $identityArr = UserIdentity::select(['income_source'])
                    ->where(['user_id' => $userId])->first();
                break;
            default :
                return $identityArr = [];
        }
        return $identityArr ? $identityArr->toArray() : [];
    }

    /**
     * @param $mobile
     * @return array
     * 根据手机号查用户信息
     */
    public static function fetchUserByMobile($mobile)
    {
        $userObj = UserAuth::where(['mobile' => $mobile])->first();
        return $userObj ? $userObj->toArray() : [];
    }

    /**
     * 每日注册量
     */
    public static function fetchRegisters()
    {
        $stime = date('Y-m-d') . ' 00:00:00';
        $etime = date('Y-m-d') . ' 23:59:59';
        $registers = UserAuth::select(['id'])
            ->where('create_at', '>=', $stime)
            ->where('create_at', '<=', $etime)
            ->count();
        return $registers ? $registers : 0;
    }


    /**
     * @param $param
     * @return array
     * 用户身份证&真实姓名&性别
     */
    public static function fetchCardAndRealname($param)
    {
        //基础信息与审核资料信息
        $basicArr = UserProfile::select(['real_name', 'identity_card', 'sex'])
            ->where(['user_id' => $param])
            ->first();
        if (!empty($basicArr)) {
            $basicArr->sex = SexStrategy::intToStr($basicArr->sex);
        }
        return $basicArr ? $basicArr->toArray() : [];
    }

    /**
     * @param $indent
     * @param $param
     * @return array
     * 学信网账号
     */
    public static function fetchXuexinWebsite($userId)
    {
        //大学生
        $certifyArr = UserCertify::select(['xuexin_website'])
            ->where(['user_id' => $userId])
            ->first();

        return $certifyArr ? $certifyArr->toArray() : [];

    }

    /**
     * @param $param
     * @return array
     * 信用卡
     */
    public static function fetchUserCredit($userId)
    {
        $certifyArr = UserCertify::select(['credit'])
            ->where(['user_id' => $userId])
            ->first();

        return $certifyArr ? $certifyArr->toArray() : [];
    }


    /**
     * 从用户主表中获取用户的手机号和是否设置密码
     * @param $mobile
     */
    public static function getMobileAndIndent($mobile)
    {
        return UserAuth::where('mobile', '=', $mobile)->where('activated', '!=', '0')->first();
    }


    /**
     * 通过手机号获取用户id
     * @param $mobile
     */
    public static function getIdByMobile($mobile)
    {
        $userAuth = UserAuth::select('sd_user_id')->where('mobile', '=', $mobile)->first();
        return $userId = isset($userAuth) ? $userAuth->sd_user_id : '';
    }

    /**
     * @param $mobile
     * @return string
     * 判断手机号是否验证验证码 & 是否注册
     */
    public static function getIdByMobileAndStatus($mobile)
    {
        $userAuth = UserAuth::select('sd_user_id')->where('mobile', '=', $mobile)
            ->where(['status' => 0])
            ->first();
        return $userId = isset($userAuth) ? $userAuth->sd_user_id : '';
    }


    /**
     * 设置用户密码和Token
     * @param $user_id
     * @param $password
     */
    public static function setUserPasswordAndToken($userId, $password)
    {
        return UserAuth::where('sd_user_id', '=', $userId)->update(['password' => $password, 'accessToken' => TokenGenerator::generateToken()]);
    }

    /**
     * 设置用户密码
     * @param $user_id
     * @param $password
     */
    public static function setUserPassword($userId, $password)
    {
        return UserAuth::where('sd_user_id', '=', $userId)->update(['password' => $password]);
    }

    /**
     * 修改用户主表中的activated 为激活状态 1
     * @param $user_id
     */
    public static function setUserActivated($userId)
    {
        return UserAuth::where('sd_user_id', '=', $userId)->update(['activated' => 1]);
    }

    /**
     * @param array $params
     * @return mixed
     * 修改用户名&身份
     */
    public static function updateUsernameAndIndent($params = [])
    {
        $userAuth = UserAuth::where(['sd_user_id' => $params['userId']])
            ->update([
                'username' => $params['username'],
                'indent' => $params['indent'],
                'update_at' => date('Y-m-d H:i:s', time()),
                'update_id' => $params['userId'],
                'update_ip' => Utils::ipAddress(),
            ]);

        return $userAuth;
    }

    /**
     * @param $params
     * @return mixed
     * 修改sd_user_auth表中的indent
     */
    public static function updateIndent($params)
    {
        $userAuth = UserAuth::where(['sd_user_id' => $params['userId']])
            ->update([
                'indent' => $params['indent'],
                'update_at' => date('Y-m-d H:i:s', time()),
                'update_id' => $params['userId'],
                'update_ip' => Utils::ipAddress(),
            ]);

        return $userAuth;
    }

    /**
     * @param array $params
     * @return mixed
     * 修改用户名
     */
    public static function updateUsername($params = [])
    {
        $userAuth = UserAuth::where(['sd_user_id' => $params['userId']])
            ->update([
                'username' => $params['username'],
                'update_at' => date('Y-m-d H:i:s', time()),
                'update_id' => $params['userId'],
                'update_ip' => Utils::ipAddress(),
            ]);

        return $userAuth;
    }

    /**
     * @param array $params
     * @return mixed
     * 修改或创建 sd_user_identity 表中的身份
     */
    public static function updateIdentity($params = [])
    {
        $userIdentity = UserIdentity::firstOrCreate(['user_id' => $params['userId']], [
            'user_id' => $params['userId'],
            'identity' => $params['indent'],
            'create_at' => date('Y-m-d H:i:s', time()),
            'create_id' => $params['userId'],
            'create_ip' => Utils::ipAddress(),
        ]);

        $userIdentity->user_id = $params['userId'];
        $userIdentity->identity = $params['indent'];
        $userIdentity->update_at = date('Y-m-d H:i:s', time());
        $userIdentity->update_id = $params['userId'];
        $userIdentity->update_ip = Utils::ipAddress();
        $userIdentity->save();

        return $userIdentity;
    }

    /**
     * @param array $params
     * @return mixed
     * 修改身份 & 是否选身份标识
     */
    public static function updateIdentityAndIsIdentity($params = [])
    {
        $userIdentity = UserIdentity::firstOrCreate(['user_id' => $params['userId']], [
            'user_id' => $params['userId'],
            'identity' => $params['indent'],
            'is_identity' => 1,
            'create_at' => date('Y-m-d H:i:s', time()),
            'create_id' => $params['userId'],
            'create_ip' => Utils::ipAddress(),
        ]);

        $userIdentity->user_id = $params['userId'];
        $userIdentity->identity = $params['indent'];
        $userIdentity->is_identity = 1;
        $userIdentity->update_at = date('Y-m-d H:i:s', time());
        $userIdentity->update_id = $params['userId'];
        $userIdentity->update_ip = Utils::ipAddress();

        return $userIdentity->save();
    }

    /**
     * @param $userId
     * @param $mobile
     * @return mixed
     * 根据 userId 修改 手机号
     */
    public static function updateMobileById($userId, $mobile)
    {
        $updateMobile = UserAuth::where(['sd_user_id' => $userId])
            ->update(['mobile' => $mobile]);

        return $updateMobile;
    }

    /**
     * @param $mobile
     * @param $pwd
     * @return mixed
     * 忘记密码 —— 修改密码
     */
    public static function updatePwdByMobile($mobile, $pwd)
    {
        $updateMobile = UserAuth::where(['mobile' => $mobile])
            ->update(['password' => $pwd]);

        return $updateMobile;
    }

    /**
     * @param $userId
     * 修改用户的访问时间visit_time
     */
    public static function updateActiveUser($userId)
    {
        $userRes = UserAuth::where(['sd_user_id' => $userId])
            ->update(['visit_time' => time()]);

        return $userRes;
    }

    /**
     * @param $params
     * 注册时添加用户身份信息
     */
    public static function createUserAgent($params)
    {
        $userAgent = new UserAgent();
        $userAgent->user_id = $params['userId'];
        $userAgent->terminal_type_id = empty($params['version']) ? 3 : $params['version'];
        $userAgent->user_agent = \App\Helpers\UserAgent::i()->getUserAgent();
        $userAgent->create_at = date('Y-m-d H:i:s', time());
        $userAgent->create_ip = Utils::ipAddress();
        return $userAgent->save();
    }

    /**
     * @param $userId
     * @return mixed
     * 单个用户针对所有产品点击立即申请数量统计
     */
    public static function updateUserCount($userId, $key)
    {
        $user = UserAuth::select()->where(['sd_user_id' => $userId])->first();
        $user->increment($key, 1);

        return $user->save();
    }

    /**
     * @param $userId
     * 论坛需要的用户信息
     */
    public static function fetchUserinfoToClubByUserId($userId)
    {
        $userinfo = UserAuth::select(['sd_user_id as user_id', 'username', 'mobile', 'accessToken', 'password'])
            ->where(['sd_user_id' => $userId])
            ->where(['status' => 0])
            ->first();
        return $userinfo ? $userinfo->toArray() : [];
    }

    /**
     * @param $userId
     * @return string
     * 获取用户密码
     */
    public static function fetchPasswordById($userId)
    {
        $password = UserAuth::select(['sd_user_id as user_id', 'password'])
            ->where(['sd_user_id' => $userId])
            ->where(['status' => 0])
            ->first();
        return $password ? $password->password : '';
    }

    /**
     * @param $userId
     * @return array
     * 获取用户头像等信息
     */
    public static function fetchPhotoById($userId)
    {
        $userinfo = UserInfo::select()
            ->where(['user_id' => $userId])
            ->first();

        return $userinfo ? $userinfo->toArray() : [];
    }

    /**
     * @param array $data
     * @return bool
     * 创建或修改用户头像
     */
    public static function createOrUpdatePhoto($data = [])
    {
        $create = UserInfo::firstOrCreate(['user_id' => $data['userId']], [
            'user_id' => $data['userId'],
            'user_photo' => $data['userPhoto'],
            'created_at' => date('Y-m-d H:i:s', time()),
            'created_ip' => Utils::ipAddress(),
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
        ]);

        $create->user_photo = $data['userPhoto'];
        $create->updated_at = date('Y-m-d H:i:s', time());
        $create->updated_ip = Utils::ipAddress();
        return $create->save();
    }

    /**
     * @param $userId
     * @return string
     * 根据用户id获取用户头像
     */
    public static function fetchUserPhotoById($userId)
    {
        $userinfo = UserInfo::select(['user_photo'])
            ->where(['user_id' => $userId])
            ->first();

        return $userinfo ? QiniuService::getImgs($userinfo->user_photo) : UserConstant::USER_PHOTO_DEFAULT;
    }

    /**
     * @param array $data
     * @return mixed
     * 判断用户名的唯一性
     */
    public static function fetchUsernameByName($data = [])
    {
        return UserAuth::select(['sd_user_id'])
//            ->whereRaw('binary username = ?', $data['username'])  // 性能太差，暂时去除
            ->whereRaw('username = ?', $data['username'])
            ->where('sd_user_id', '<>', $data['userId'])
            ->first();
    }

    /**
     * 将没有设置密码的设置上密码
     * @param array $params
     * @return mixed
     */
    public static function updatePasswordById($params = [])
    {
        return UserAuth::where('sd_user_id', '=', $params['sd_user_id'])
            ->where(['password' => ''])
            ->update(['password' => $params['password']]);
    }
}
