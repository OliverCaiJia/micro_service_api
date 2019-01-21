<?php

namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Creditcard\Account\DoAccountHandler;
use App\Models\Chain\Creditcard\Bill\DoBillHandler;
use App\Models\Factory\CreditcardAccountFactory;
use App\Strategies\BanksStrategy;
use App\Strategies\CreditcardAccountStrategy;
use Illuminate\Http\Request;

/**
 * Class CreditcardAccountController
 * @package App\Http\Controllers\V1
 * 信用卡账户
 */
class CreditcardAccountController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 修改信用卡账户前显示数据
     */
    public function fetchBeforeAccount(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        $account = CreditcardAccountFactory::fetchBeforeAccount($data);
        //暂无信用卡信息
        if (!$account) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        $account = CreditcardAccountStrategy::getBeforeAccount($account);
        return RestResponseFactory::ok($account);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 添加或修改信用卡账户信息
     */
    public function createOrUpdateAccount(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        $data['accountId'] = $request->input('accountId', 0);
        //查询信用卡数量 大于15张不允许继续添加
        $count = CreditcardAccountFactory::fetchAccountCount($data);
        if (empty($data['accountId']) && $count >= 15) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2300), 2300);
        }

        //调用责任链
        $creditCash = new DoAccountHandler($data);
        $re = $creditCash->handleRequest();
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }

        $res['account_id'] = isset($re['account_id']) ? $re['account_id'] : 0;
        $res['bank_logo'] = isset($re['bank_logo']) ? $re['bank_logo'] : '';
        return RestResponseFactory::ok($res);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 修改提醒状态
     */
    public function updateRepayAlertStatus(Request $request)
    {
        $data['repayAlertStatus'] = $request->input('repayAlertStatus', 0);
        $data['userId'] = $request->user()->sd_user_id;
        $data['accountId'] = $request->input('accountId', 0);

        $status = CreditcardAccountFactory::updateRepayAlertStatus($data);
        if (!$status) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 添加Or修改账单
     */
    public function createOrUpdateBill(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        //调用责任链
        $creditCash = new DoBillHandler($data);
        $re = $creditCash->handleRequest();
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }
        $res['bill_id'] = isset($re['bill_id']) ? $re['bill_id'] : 0;
        return RestResponseFactory::ok($res);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 修改账单状态为已还
     */
    public function updateBillStatus(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['billId'] = $request->input('billId');
        //修改
        $updateBill = CreditcardAccountFactory::updateBillStatus($data);

        if (!$updateBill) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 信用卡账单列表
     */
    public function fetchAccountBills(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //获取用户的信用卡&账单
        $accounts = CreditcardAccountFactory::fetchAccountsByUserId($data);
        //暂无数据
        if (!$accounts) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //额度转为空
        $accounts = CreditcardAccountStrategy::getRepayAccount($accounts);
        //处理数据 银行logo
        $accounts = BanksStrategy::getBankLogo($accounts);
        //未还账单
        $accountbills = CreditcardAccountFactory::fetchAccountbills($accounts, $data);
        //已还账单
        $accountbills = CreditcardAccountFactory::fetchAccountbilleds($accountbills, $data);
        //日期数据转化
        $accountbills = CreditcardAccountStrategy::getAccountbills($accountbills);
        //将年限相同的数据放到一个数组中
        //$accountbills = CreditcardAccountStrategy::getAccountbillsByYear($accountbills);
        //按照年份从大到小进行排序
        //$lists = CreditcardAccountStrategy::getAccountbillsOrderByYear($accountbills);
        //dd($lists);
        return RestResponseFactory::ok($accountbills);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 已还账单更多列表
     */
    public function fetchBills(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);

        //点击更多 获取账单列表
        $bills = CreditcardAccountFactory::fetchBills($data);
        $pageCount = $bills['pageCount'];
        if (!$bills) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //已还账单时间转化
        $bills = CreditcardAccountStrategy::getBills($bills['list']);
        //将相同年限的数据放到同个一个数组中
        $bills = CreditcardAccountStrategy::getBillsByYear($bills);
        $bills = array_values($bills);
        ksort($bills);

        $datas['list'] = $bills;
        $datas['pageCount'] = $pageCount;
        return RestResponseFactory::ok($datas);
    }

}