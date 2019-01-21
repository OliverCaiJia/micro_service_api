<?php

namespace App\Http\Controllers\V1;

use App\Constants\CreditConstant;
use App\Helpers\Formater\NumberFormater;
use App\Helpers\LinkUtils;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\ProductApply\DoProductApplyHandler;
use App\Models\Chain\Urge\DoUrgeHandler;
use App\Models\Factory\BankFactory;
use App\Models\Factory\ConfigFactory;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\AccountFactory;
use App\Models\Factory\CreditStatusFactory;
use App\Models\Factory\InviteFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserSignFactory;
use App\Models\Orm\SystemConfig;
use App\Models\Orm\UserCredit;
use App\Models\Orm\UserInvite;
use App\Models\Chain\Credit\DoCreditHandler;
use App\Strategies\CreditStrategy;
use App\Strategies\InviteStrategy;
use App\Strategies\UserStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \App\Strategies\IdentityStrategy;

/**
 * Class CreditController
 * @package App\Http\Controllers\V1
 * 积分
 */
class CreditController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 积分首页
     */
    public function fetchCreditIndexs(Request $request)
    {
        /**
         * 通過Token獲取用戶ID
         */
        $userId = $request->user()->sd_user_id;
        /**
         * 账号余额
         */
        $userAccount = AccountFactory::fetchBalance($userId);
        $creditArr['userAccount'] = NumberFormater::formatMoney($userAccount);
        /**
         * 账号积分
         */
        $userScore = CreditFactory::fetchCredit($userId);
        $creditArr['userScore'] = intval($userScore);
        /**
         *  用户信息
         */
        $userProfile = UserFactory::fetchRealNameAndSex($userId);
        $creditArr['sex'] = $userProfile['sex'];
        $creditArr['realname'] = $userProfile['realname'];
        /**
         *  用户信息
         */
        $userAuth = UserFactory::fetchUserNameAndMobile($userId);
        $creditArr['username'] = $userAuth['username'];
        $creditArr['mobile'] = $userAuth['mobile'];
        return RestResponseFactory::ok($creditArr);
    }

    /**
     * @param Request $request
     * @return mixed
     * 积分页
     */
    public function fetchCredits(Request $request)
    {
        /**
         * 通過Token獲取用戶ID
         */
        $userId = $request->user()->sd_user_id;
        //$userId = 321;
        $indent = UserFactory::fetchUserIndent($userId);
        /**
         * 完善个人信息
         */

        //用户信息——基础信息
        $userProfile = UserFactory::fetchUserProfile($userId);
        //用户信息——审核资料信息
        $userCertify = UserFactory::fetchUserCertify($indent, $userId);
        //用户信息——个人信息
        $identityArr = UserFactory::fetchUserIdentity($indent, $userId);
        //银行卡信息
        $bankArr = BankFactory::fetchBanksArray($userId);
        //支付宝信息
        $alipayArr = BankFactory::fetchAlipayArray($userId);

        //进度
        $progress = UserStrategy::fetchProgress($indent, $userProfile, $userCertify, $identityArr, $bankArr, $alipayArr);
        $progress = $progress['userInfoCounts'];
        $creditArr['info_sign'] = IdentityStrategy::toInfoSign($indent, $progress);
        /**
         * 用户信息
         */
        $creditArr['max_score'] = CreditFactory::fetchCreditMax();
        $creditArr['user_score'] = CreditFactory::fetchCredit($userId);
        /**
         * 邀请好友
         */
        $invite_num = InviteFactory::fetchUserInvitations($userId);
        $inviteCode = InviteFactory::fetchInviteCode($userId);
        $creditArr['invite'] = InviteStrategy::toInviteSign($invite_num, $inviteCode);
        /**
         * 产品申请
         */
        //产品申请id
        $creditProId = CreditFactory::fetchProductApplyId();
        //查询下线产品id
        $productId = ProductFactory::updateProductApply($creditProId);
        if (!empty($productId)) {
            CreditFactory::updateProductApplyStatus($productId);
        }
        $productApplyArr = CreditFactory::fetchProductApply();
        $creditArr['product_apply'] = CreditStrategy::getProductApply($userId, $productApplyArr);
        //分享
        $creditArr['share_link'] = LinkUtils::shareOnlyLink();
        //h5 单独使用
        $creditArr['h5_only_share_link'] = LinkUtils::shareLanding($inviteCode);
        //积分页 描述

        $creditArr['credit_remark'] = ConfigFactory::getExtraData(CreditConstant::CREDIT_REMARK_TYPE);
        return RestResponseFactory::ok($creditArr);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 积分兑现金  查询
     */
    public function fetchCash(Request $request)
    {

        $userId = $request->user()->sd_user_id;
        //$userId = 321;
        /**
         * 用户信息
         */
        $cashArr['max_score'] = CreditFactory::fetchCreditMax();
        $cashArr['user_score'] = CreditFactory::fetchCredit($userId);
        $cashArr['cash_credits'] = CreditConstant::EXCHANGE_CREDIT;
        $cashArr['cash_money'] = CreditConstant::EXCHANGE_MONRY;
        return RestResponseFactory::ok($cashArr);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 积分兑现金  兑换
     */
    public function createCreditCash(Request $request)
    {
        //通过token获取用户id
        $userId = $request->user()->sd_user_id;
        $data = $request->all();
        $data['userId'] = $userId;
        $creditCash = new DoCreditHandler($data);
        $re = $creditCash->handleRequest();
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }
        return RestResponseFactory::ok($re);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 积分页  产品申请加积分
     */
    public function createApply(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        $data = $request->all();
        $data['userId'] = $userId;
        //产品申请加积分
        $smsInvite = new DoProductApplyHandler($data);
        $re = $smsInvite->handleRequest();
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }
        return RestResponseFactory::ok($re);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 催审扣积分
     */
    public function reduceCreditsByUrge(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        $data['urgeId'] = $request->input('urgeId');
        //催审扣五积分
        $data['expend'] = $request->input('expend', 5);
        $data['user_id'] = $userId;

        //已为您加速审核，请保持电话畅通~
        $urge = ProductFactory::fetchUrgeById($data);
        if ($urge) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(6100), 6100);
        }
        //点击催审减积分
        $smsInvite = new DoUrgeHandler($data);
        $re = $smsInvite->handleRequest();
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 赚积分列表
     */
    public function fetchAddIntegrals(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        $params['userId'] = $userId;
        //用户总收入积分
        $income = CreditFactory::fetchIncomeByUserId($userId);
        //列表
        //新人注册
        $typeNid = CreditConstant::ADD_INTEGRAL_USER_REGISTER_TYPE;
        $params['typeId'] = CreditFactory::fetchIdByTypeNid($typeNid);
        $data[0]['score'] = CreditFactory::fetchScoreByTypeNid($typeNid);
        $data[0]['credit_sign'] = 1;
        //设置头像
        $typeNid = CreditConstant::ADD_INTEGRAL_USER_PHOTO_TYPE;
        $params['typeId'] = CreditFactory::fetchIdByTypeNid($typeNid);
        $data[1]['score'] = CreditFactory::fetchScoreByTypeNid($typeNid);
        $data[1]['credit_sign'] = CreditStatusFactory::fetchCreditStatusByUserId($params);
        //设置用户名
        $typeNid = CreditConstant::ADD_INTEGRAL_USER_USERNAME_TYPE;
        $params['typeId'] = CreditFactory::fetchIdByTypeNid($typeNid);
        $data[2]['score'] = CreditFactory::fetchScoreByTypeNid($typeNid);
        $data[2]['credit_sign'] = CreditStatusFactory::fetchCreditStatusByUserId($params);
        //每日签到
        $typeNid = CreditConstant::ADD_INTEGRAL_USER_SIGN_TYPE;
        $params['typeId'] = CreditFactory::fetchIdByTypeNid($typeNid);
        $data[3]['score'] = CreditFactory::fetchScoreByTypeNid($typeNid);
        $data[3]['credit_sign'] = UserSignFactory::fetchUserSignByUserId($userId);
        //发表评论
        $typeNid = CreditConstant::ADD_INTEGRAL_USER_COMMENT_TYPE;
        $params['typeId'] = CreditFactory::fetchIdByTypeNid($typeNid);
        $data[4]['score'] = CreditFactory::fetchScoreByTypeNid($typeNid);
        $data[4]['credit_sign'] = 0;
        //推荐新贷款产品
        $typeNid = CreditConstant::ADD_INTEGRAL_FEEDBACK_PRODUCT_NAME_TYPE;
        $params['typeId'] = CreditFactory::fetchIdByTypeNid($typeNid);
        $data[5]['score'] = CreditFactory::fetchScoreByTypeNid($typeNid);
        $data[5]['credit_sign'] = 0;
        //意见反馈
        $typeNid = CreditConstant::ADD_INTEGRAL_FEEDBACK_TYPE;
        $params['typeId'] = CreditFactory::fetchIdByTypeNid($typeNid);
        $data[6]['score'] = CreditFactory::fetchScoreByTypeNid($typeNid);
        $data[6]['credit_sign'] = 0;

        $datas['income'] = $income;
        $datas['list'] = $data;

        return RestResponseFactory::ok($datas);
    }


}