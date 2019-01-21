<?php

namespace App\Http\Controllers\V1;


use App\Constants\ConfigConstant;
use App\Helpers\Formater\NumberFormater;
use App\Helpers\LinkUtils;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\AccountFactory;
use App\Models\Factory\AlipayFactory;
use App\Models\Factory\BankFactory;
use App\Models\Factory\CashFactory;
use App\Models\Chain\Cash\DoCashHandler;
use App\Models\Factory\ConfigFactory;
use App\Models\Factory\UserFactory;
use App\Strategies\AccountLogStrategy;
use App\Strategies\CashStrategy;
use App\Strategies\IdentityStrategy;
use App\Strategies\UserStrategy;
use Illuminate\Http\Request;

/**
 * Class AccountController
 * @package App\Http\Controllers\V1
 * 用户账户
 */
class AccountController extends Controller
{
    /**
     * @param Request $request
     * 用户账户信息&&提现流水
     */
    public function fetchMyAccountAndLog(Request $request)
    {
        $userId = $request->user()->sd_user_id;

        $userAccount = [];
        //身份
        $indent = UserFactory::fetchUserIndent($userId);
        //用户身份证&真实姓名
        $userProfile = UserFactory::fetchCardAndRealname($userId);
        //用户是否拥有信用卡或是学信网账号
        if($indent == 1) {
            $userCertify = UserFactory::fetchXuexinWebsite($userId);
        }else {
            $userCertify = UserFactory::fetchUserCredit($userId);
        }

        //银行卡信息
        $bankArr = BankFactory::fetchBanksArray($userId);
        //支付宝信息
        $alipayArr = BankFactory::fetchAlipayArray($userId);

        //进度
        $progress = UserStrategy::getMerges($userProfile, $userCertify, $bankArr, $alipayArr);
        $userAccount['info_sign'] = IdentityStrategy::toBasicSign($progress);
        //用户账户现金
        $userBalance = AccountFactory::fetchBalance($userId);
        $userAccount['user_account'] = NumberFormater::formatMoney($userBalance);
        //提现流水  最后10条
        $accountCash = CashFactory::fetchUserAccountCash();
        $userAccount['account_cash'] = CashStrategy::getMobileAndTotal($accountCash);
        //获取支付宝账号
        $userAccount['alipay'] = AlipayFactory::getAlipay($userId);
        //用户账户流水
        $accountLogArr = AccountFactory::fetchUserAccountLog($userId);
        $userAccount['account_log'] = AccountLogStrategy::getLogDatas($accountLogArr);
        //额外现金奖励值
        $userAccount['extra_money'] = ConfigFactory::getExtraData(ConfigConstant::CONFIG_EXTRA);

        return RestResponseFactory::ok($userAccount);
    }

    /**
     * @return mixed
     * 账户提现 规则
     */
    public function getRules()
    {
        $rule = LinkUtils::getAccountRule();
        $data['account_rule'] = $rule ? $rule : '';
        return RestResponseFactory::ok($data);
    }

    /**
     * @param Request $request
     * 账户提现   用户账户信息
     */
    public function fetchUserAccounts(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        //获取支付宝账号
        $userAccount['alipay'] = AlipayFactory::getAlipay($userId);
        //用户账户余额
        $userBalance = AccountFactory::fetchBalance($userId);
        $userAccount['user_account'] = NumberFormater::formatMoney($userBalance);
        return RestResponseFactory::ok($userAccount);
    }

    /**
     * @param Request $request
     * 账户提现  提现
     */
    public function updateUserAccounts(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        $data = $request->all();
        $data['userId'] = $userId;
        $creditCash = new DoCashHandler($data);
        $re = $creditCash->handleRequest();
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }
        return RestResponseFactory::ok($re);
    }

}
