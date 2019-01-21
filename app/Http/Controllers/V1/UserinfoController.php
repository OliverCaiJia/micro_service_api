<?php
namespace App\Http\Controllers\V1;

use App\Events\V1\UserinfoPushEvent;
use App\Events\V1\UserPushEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\BankFactory;
use App\Models\Factory\LocationFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserinfoFactory;
use App\Strategies\BankStrategy;
use App\Strategies\UserinfoStrategy;
use Composer\Cache;
use Illuminate\Http\Request;

/**
 * Class UserinfoController
 * @package App\Http\Controllers\V1
 * 用户信息控制器
 */
class UserinfoController extends Controller
{
    /**
     * @param Request $request
     * 基础信息 —— 查询用户基础信息
     */
    public function fetchBasicinfo(Request $request)
    {
        $userId = $request->user()->sd_user_id;

        //用户基础信息
        $basicArr = UserinfoFactory::fetchBasicinfo($userId);
        //根据 userId 查询 Account
        $userAccount = BankFactory::fetchBanksArray($userId);
        //银行卡信息 Name
        $bankArr = BankFactory::fetchBankNameByBankId($userAccount);
        //数据处理 得到 Account & Name
        $userBanksArr = BankStrategy::getAccountAndName($userAccount, $bankArr);
        //支付宝
        $alipay = BankFactory::fetchAlipay($userId);
        //用户手机号
        $mobile = UserFactory::fetchMobile($userId);
        //是否拥有信用卡或是学信网账号
        $certifyArr = UserinfoFactory::fetchXuexinAndCredit($userId);

        //进度条计算
        $progCounts = UserinfoFactory::fetchProgress($userId);
        //数据整理
        $userData = UserinfoStrategy::getBasicinfo($basicArr, $userBanksArr, $alipay, $mobile, $certifyArr, $progCounts);

        if (empty($userData)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($userData);
    }

    /**
     * @param Request $request
     * 基础信息 —— 修改用户基础信息
     */
    public function updateBasicinfo(Request $request)
    {
        $data   = $request->all();
        $userId = $request->user()->sd_user_id;

        // 修改基础信息
        $basic = UserinfoFactory::updateProfile($data, $userId);
        // 修改学信网 || 信用卡
        $certify = UserinfoFactory::updateCertify($data, $userId);
        // 修改、添加银行卡号
        $banks = UserinfoFactory::updateUserBanks($data, $userId);
        // 修改、添加支付宝账号
        $alipay = UserinfoFactory::updateUserAlipay($data, $userId);

        if (empty($basic) || empty($certify) || empty($banks) || empty($alipay)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1), 1);
        }

        //进度条计算
        $progCounts = UserinfoFactory::fetchProgress($userId);
        // 数据整合
        $progArr = UserinfoFactory::fetchProgressArray($progCounts);

        //监听推送事件
        $push['progArr'] = $progArr;
        $push['userId']  = $userId;

        event(new UserPushEvent(['push' => $push]));

        return RestResponseFactory::ok($progArr);
    }

    /**
     * @param Request $request
     * 信用信息 —— 查询用户信用信息
     */
    public function fetchIdentityinfo(Request $request)
    {
        $userId = $request->user()->sd_user_id;

        //信用信息————个人信息
        $profileArr = UserinfoFactory::fetchUserProfile($userId);
        if(empty($profileArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(),RestUtils::getErrorMessage(1500),1500);
        }
        //信用信息————职业信息
        $indent = UserFactory::fetchUserIndent($userId);
        //信用信息————个人信息数据处理
        $userProfileArr = UserinfoStrategy::getProfileinfo($profileArr);

        //信用信息————职业信息数据处理
        $datas = UserinfoFactory::fetchIdentityinfo($userId, $indent, $userProfileArr);

        return RestResponseFactory::ok($datas);
    }

    /**
     * @param Request $request
     * 信用信息 —— 创建或修改用户信用信息
     */
    public function updateIdentityinfo(Request $request)
    {
        $data   = $request->all();
        $userId = $request->user()->sd_user_id;

        //定位统计
        LocationFactory::createLocation($data, $userId);
        //修改信用信息中的个人信息
        $profileRes = UserinfoFactory::updateProfilesOfIdentity($userId, $data);

        //身份
        $indent    = UserFactory::fetchUserIndent($userId);
        //职业信息
        $creditRes = UserinfoFactory::updateIdentityById($userId, $indent, $data);

        //进度条计算
        $progCounts = UserinfoFactory::fetchProgress($userId);
        // 数据整合
        $progArr = UserinfoFactory::fetchProgressArray($progCounts);

        //监听推送事件
        $push['progArr'] = $progArr;
        $push['userId']  = $userId;
        event(new UserPushEvent(['push' => $push]));

        return RestResponseFactory::ok($progArr);
    }

    /**
     * @param Request $request
     * 审核资料 —— 查询用户审核资料
     */
    public function fetchCertifyinfo(Request $request)
    {
        $userId = $request->user()->sd_user_id;

        $cerArr = UserinfoFactory::fetchUserCertify($userId);
        if(empty($cerArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(),RestUtils::getErrorMessage(1500),1500);
        }
        //数据处理
        $certifyArr = UserinfoStrategy::getCertifyinfo($cerArr);
        
        return RestResponseFactory::ok($certifyArr);
    }

    /**
     * @param Request $request
     * 审核资料 —— 创建&修改 用户审核资料
     */
    public function updateCertifyinfo(Request $request)
    {
        $data   = $request->all();
        $userId = $request->user()->sd_user_id;

        //修改数据
        $certify = UserinfoFactory::updateCertityinfo($data,$userId);

        if(empty($certify)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(),RestUtils::getErrorMessage(2105),2105);
        }

        //进度条计算
        $progCounts = UserinfoFactory::fetchProgress($userId);
        // 数据整合
        $progArr = UserinfoFactory::fetchProgressArray($progCounts);

        //监听推送事件
        $push['progArr'] = $progArr;
        $push['userId']  = $userId;
        event(new UserPushEvent(['push' => $push]));

        return RestResponseFactory::ok($progArr);
    }


}