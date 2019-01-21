<?php

namespace App\Http\Controllers\View;

use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;
use App\Models\Orm\UserBill;
use App\Strategies\UserBillPlatformStrategy;
use App\Strategies\UserBillStrategy;
use Illuminate\Http\Request;

/**
 * Class UserLoanAccountController
 * @package APP\Http\Controllers\V1
 *  用户贷款账户
 */
class UserBillController extends Controller
{
    /**
     * 负债分析图标
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function fetchBillAnalysis(Request $request)
    {
        $data['sign'] = $request->user()->accessToken;
        //年
        $data['year'] = date('Y', time());
        //月
        $data['month'] = date('m', time());

        return view('app.jdt.user_bill.import_bill_analysis', ['data' => $data]);
    }


    /**
     * 根据平台id获取账单列表 信用卡账单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function fetchCreditcardBills(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['sign'] = $request->user()->accessToken;

        $data['creditcardId'] = $request->input('creditcardId');
        //分页
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 1);

        //关系表查询账单ids
        $data['billIds'] = UserBillFactory::fetchRelBillIdsById($data['creditcardId']);
        //查询改信用卡平台下账单列表
        $userBills = UserBillFactory::fetchCreditcardBills($data);

        if (!$userBills['list']) {
            $error_meg = RestUtils::getErrorMessage(20001);
            return view('app.jdt.errors.error_static', ['error' => $error_meg]);
        }
        $pageCount = $userBills['pageCount'];
        //数据处理
        $userBills = UserBillStrategy::getCreditcardBills($userBills['list']);

        $res['list'] = $userBills;
        $res['pageCount'] = $pageCount;

        return view('app.jdt.user_bill.import_bill_detail', ['data' => $data]);
    }

    /**
     * 导入信用卡账单结果数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function fetchBillImportResults(Request $request)
    {
        $userId = $request->user()->sd_user_id;
//        $userId = 1702;
        //共找到n家银行n封账单
        $result = UserBillPlatformFactory::fetchImportBankIds($userId);
        //数组去重
        $bankIds = array_unique($result['bankIds']);

        //账单银行总数
        $banksTotal = count($bankIds);
        //新账单总数
        $newBillCounts = intval($result['newBillCounts']);
        //导入方式 邮箱or网银
        $tasks = UserBillPlatformFactory::fetchBillTask($userId);

        $importType = empty($tasks['email_onlinebank']) ? '' : $tasks['email_onlinebank'];
        //简版账单数量
        $simple_bill_num = empty($tasks['simple_bill_num']) ? 0 : $tasks['simple_bill_num'];
        //采集完成 没有账单数据
        if ($tasks && $tasks['step'] == 6 && $tasks['is_complete'] == 1) {
            $error_meg = RestUtils::getErrorMessage(20000);
            return view('app.jdt.errors.error_static', ['error' => $error_meg]);
        }

        //提示简版账单
        if (empty($bankIds) && $tasks && $tasks['step'] == 5 && $tasks['is_complete'] == 1 && !empty($simple_bill_num)) {
            $results['banksTotal'] = 0;
            $results['newBillCounts'] = 0;
            $results['importType'] = $importType;
            $results['notBeyonds'] = [];
            $results['beyonds'] = [];
            $results['beyondCounts'] = 0;
            $results['simple_bill_num'] = $simple_bill_num;

            return view('app.jdt.user_bill.import_bill_results', ['data' => $results]);
        }

        //暂无数据
        if (empty($bankIds)) {
            $error_meg = RestUtils::getErrorMessage(20000);
            return view('app.jdt.errors.error_static', ['error' => $error_meg]);
        }

        //未超出15张  账单平台信用卡数据
        $data['userId'] = $userId;
        $data['bank_is_besides'] = 0;
        //信用卡平台信息
        $creditcards = UserBillPlatformFactory::fetchCreditcardImportResultsByUserId($data);
        //账单数据
        $notBeyonds = UserBillPlatformFactory::fetchBillImportResults($creditcards);
        //数据处理
        $notBeyonds = UserBillPlatformStrategy::getBillImportResults($notBeyonds);

        //超过15张 账单平台信用卡数据
        $data['userId'] = $userId;
        $data['bank_is_besides'] = 1;
        //信用卡平台信息
        $creditcards = UserBillPlatformFactory::fetchCreditcardImportResultsByUserId($data);
        //账单数据
        $beyonds = UserBillPlatformFactory::fetchBillImportResults($creditcards);
        //数据处理
        $beyonds = UserBillPlatformStrategy::getBillImportResults($beyonds);

        //整理数据
        $results['banksTotal'] = $banksTotal;
        $results['newBillCounts'] = $newBillCounts;
        $results['importType'] = $importType;
        $results['notBeyonds'] = $notBeyonds;
        $results['beyonds'] = $beyonds;
        $results['beyondCounts'] = count($beyonds);
        $results['simple_bill_num'] = $simple_bill_num;

        return view('app.jdt.user_bill.import_bill_results', ['data' => $results]);
    }
}
