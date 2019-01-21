<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserBankcardConstant;
use App\Helpers\LinkUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Chain\UserBank\Defaultcard\DoDefaultcardHandler;
use App\Models\Chain\UserBank\Delete\DoDeleteHandler;
use App\Models\Chain\UserBank\Add\DoAddHandler;
use App\Models\Chain\UserBank\LastPay\DoLastPayHandler;
use App\Services\Core\Payment\PaymentService;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\UserBankCardStrategy;
use Illuminate\Http\Request;
use App\Helpers\RestResponseFactory;
use App\Models\Factory\UserBankCardFactory;
use App\Helpers\RestUtils;

/**
 * Class UserBankCardController
 * @package App\Http\Controllers\V1
 * 用户银行卡绑定
 */
class UserBankCardController extends Controller
{
    /**
     * 银行卡四要素验证并添加银行卡或更换银行卡
     * params:cardType,mobile,cardNum,cardId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrUpdateUserBanksById(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['cardType'] = $request->input('cardType', 1);
        $data['account'] = $request->input('account', '');
        $data['mobile'] = $request->input('mobile', '');
        $data['shadow_nid'] = $request->input('shadowNid', '');
        //更换
        $data['replace'] = $request->input('replace', '');
        $data['userbankId'] = $request->input('userbankId');

        //银行卡责任链
        $bankcard = new DoAddHandler($data);
        $res = $bankcard->handleRequest();
        //错误提示
        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }

        return RestResponseFactory::ok($res);
    }


    /**
     * 银行卡列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchUserBanks(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['cardType'] = $request->input('cardType');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);

        //用户绑定银行卡列表
        $userbanks = UserBankCardFactory::fetchUserbanks($data);
        $pageCount = $userbanks['pageCount'];
        //暂无数据
        $cards = [];
        if (!empty($userbanks['list'])) {
            //获取银行信息，用户信息
            $cards = UserBankCardFactory::fetchUserbanksinfo($userbanks['list']);
            //数据处理
            $cards = UserBankCardStrategy::getUserbanksinfo($cards);
        }

        $banks['list'] = $cards;
        $banks['pageCount'] = $pageCount;
        if ($data['cardType'] == 1) {
            //储蓄卡
            $banks['quota_bank_link'] = LinkUtils::quotaSavingCardBankLink();
        } else {
            //信用卡
            $banks['quota_bank_link'] = LinkUtils::quotaCreditCardBankLink();
        }

        return RestResponseFactory::ok($banks);
    }


    /**
     * 删除银行卡，若删除默认银行卡则设置最近一张银行卡为默认银行卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUserBankById(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //银行卡类型
        $data['cardType'] = $request->input('cardType');
        $data['userbankId'] = $request->input('userbankId');

        //删除银行卡责任链
        $deleteCard = new DoDeleteHandler($data);
        $res = $deleteCard->handleRequest();
        //错误提示
        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


    /**
     * 设置默认储蓄卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBankcardDefaultById(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['userbankId'] = $request->input('userbankId');

        //设置默认储蓄卡责任链
        $card = new DoDefaultcardHandler($data);
        $res = $card->handleRequest();
        //错误提示
        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


    /**
     * 银行卡校验，并获取所在银行和logo
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkBankCardNum(Request $request)
    {
        $bankcardNum = $request->input('account');
        //去除所有空格
        $bankcardNum = Utils::removeSpace($bankcardNum);
        //1储蓄卡，2信用卡
        $cardType = $request->input('cardType', 1);
        $shadowNid = $request->input('shadowNid', '');
        $data['cardno'] = $bankcardNum;
        //调用易宝支付卡号验证
        $res = PaymentService::i($shadowNid)->extraInterface($data);

        //开户银行查不出，开户无效
        if (empty($res) || $res['isvalid'] == 0) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1129), 1129);
        }

        //银行卡类型不一致
        if ($res['cardtype'] != $cardType) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1131), 1131);
        }
        //获取logo
        $bankinfo = UserBankCardFactory::getBankInfoByBankcode($res['bankcode']);
        //银行不支持，本地没有该银行信息
        if (!$bankinfo) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1128), 1128);
        }
        $bankdata = [
            'bankname' => empty($bankinfo['sname']) ? $bankinfo['name'] : $bankinfo['sname'],
            'cardType' => $res['cardtype'],
            'banklogo' => QiniuService::getImgs($bankinfo['litpic']),
        ];
        return RestResponseFactory::ok($bankdata);
    }

    /**
     * 修改支付银行卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCardLastStatus(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['userbankId'] = $request->input('userbankId');

        //更换支付卡责任链
        $card = new DoLastPayHandler($data);
        $res = $card->handleRequest();
        //错误提示
        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 信用卡 —— 支持银行及限额
     * @return \Illuminate\Http\JsonResponse
     */
    public static function fetchQuotaCreditCardBanks()
    {
        $banks = UserBankcardConstant::QUOTA_BANKS_CREDIT_CARD;
        //暂无数据
        if (empty($banks)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($banks);
    }

    /**
     * 储蓄卡 —— 支持银行及限额
     * @return \Illuminate\Http\JsonResponse
     */
    public static function fetchQuotaSavingCardBanks()
    {
        $banks = UserBankcardConstant::QUOTA_BANKS_SAVING_CARD;
        //暂无数据
        if (empty($banks)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($banks);
    }

}