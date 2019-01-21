<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserBillPlatformConstant;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Chain\UserBill\Creditcard\DoCreditcardHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;
use App\Strategies\UserBillPlatformStrategy;
use App\Strategies\UserBillStrategy;
use Illuminate\Http\Request;

/**
 * 用户账单相关
 * Class UserBillController
 * @package App\Http\Controllers\V1
 */
class UserBillController extends Controller
{
    /**
     * 创建或修改信用卡账单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrUpdateCreditcardBill(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        $data['billId'] = $request->input('billId', '');

        $bill = new DoCreditcardHandler($data);
        $re = $bill->handleRequest();
        //错误提示
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }
        return RestResponseFactory::ok($re);
    }

    /**
     * 根据平台id获取账单列表 信用卡账单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCreditcardBills(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
//        $data['userId'] = 1831;
        //信用卡平台id
        $data['creditcardId'] = $request->input('creditcardId');
        //分页
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);

        //关系表查询账单ids
        $data['billIds'] = UserBillFactory::fetchRelBillIdsById($data['creditcardId']);
        //查询改信用卡平台下账单列表
        $userBills = UserBillFactory::fetchCreditcardBills($data);

        if (!$userBills['list']) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $pageCount = $userBills['pageCount'];
        //数据处理
        $userBills = UserBillStrategy::getCreditcardBills($userBills['list']);

        $res['list'] = $userBills;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }

    /**
     * 账单明细
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function fetchCreditcardBillDetails(Request $request)
    {
        //信用卡平台id
        $data['billId'] = $request->input('billId');
        //分页
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 20);

        //处理账单明细内部分页
        $userBills = UserBillFactory::fetchCreditcardBillDetails($data);

        if (empty($userBills)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //导入信用卡账单数据
        $details = json_decode($userBills, true);
        $billDetails = DateUtils::pageInfo($details, $data['pageSize'], $data['pageNum']);
        $pageCount = $billDetails['pageCount'];

        //处理数据
        $detail = UserBillStrategy::getCreditcardBillDetail($billDetails['list']);
        $res['list'] = $detail;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }

    /**
     * 账单导入邮箱、网银列表
     * @importType 导入类型 0邮箱，1网银
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchImportTypeData(Request $request)
    {
        //导入类型 0邮箱，1网银
        $importType = $request->input('importType', 0);
        if (empty($importType)) {
            //邮箱导入
            $importData = UserBillFactory::fetchImportBillMails();
            if (!$importData) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
            }
            //数据处理
            $importData = UserBillStrategy::getImportBillMails($importData);
        } else {
            //网银导入
            //账单银行唯一标识
            $typeNid = UserBillPlatformConstant::BILL_IMPORT_CYBER_BANK;
            //账单银行类型id
            $typeId = UserBillPlatformFactory::fetchBillBankTypeIdByNid($typeNid);
            //获取导入账单网银列表
            $importData = UserBillPlatformFactory::fetchBanksByTypeId($typeId);
            //暂无数据
            if (!$importData) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
            }
            //数据处理
            $importData = UserBillPlatformStrategy::getImportCyberBanks($importData);
        }

        return RestResponseFactory::ok($importData);
    }

    /**
     * 任务采集步骤通知
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBillInfoStatus(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        $tasks = UserBillPlatformFactory::fetchBillTask($userId);
        $data['step_sign'] = 0;
        //采集完成 没有账单数据
        if ($tasks && $tasks['step'] == 6 && $tasks['is_complete'] == 1) {
            $data['step_sign'] = 1;
        }
        //step步骤是5，is_complete是1表示数据采集完成
        if ($tasks && $tasks['step'] == 5 && $tasks['is_complete'] == 1) {
            $data['step_sign'] = 1;
        }

        return RestResponseFactory::ok($data);
    }

    /**
     * 首页列表数据处理 【全部，信用卡】
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCreditcardUserbills(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        $data['deleteIds'] = $request->input('deleteIds', '');
        // 账单类型 0全部， 1信用卡
        $data['billType'] = $request->input('billType', 0);

        //列表
        //手动输入 平台ids
        $data['bank_is_import'] = 0;
        $data['is_hidden'] = 0;
        $inputPlatformIds = UserBillPlatformFactory::fetchBillPlatformIdsByUserId($data);
        //平台id对应的账单id
        $inputBillIds = UserBillFactory::fetchRelBillIdsByPlatformIds($inputPlatformIds);
        //符合条件的账单ids
        $inputBillIds = UserBillFactory::fetchInputBillIdsByBillIds($inputBillIds);

        //导入 平台ids
        $data['bank_is_import'] = 1;
        $data['is_hidden'] = 0;
        $importPlatformIds = UserBillPlatformFactory::fetchBillPlatformIdsByUserId($data);
        //导入的平台下对应的相应的最新的账单id
        $importBillIds = UserBillFactory::fetchImportBillIdsByPlatformIds($importPlatformIds);
        //合并符合的账单id
        $billIds = array_merge($inputBillIds, $importBillIds);

        //删除排序
        if (!empty($data['deleteIds'])) {
            $deleteIds = explode(',', $data['deleteIds']);
            $billIds = array_merge($deleteIds, $billIds);
        }
        //去重
        $data['billIds'] = array_unique($billIds);

        if (empty($data['billIds'])) {
            //暂无数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //求未还平台列表数据
        $waitBills = UserBillFactory::fetchWaitBills($data);
        //求已还平台列表数据
        $already = UserBillFactory::fetchAlreadyCreditcardBills($data);
        //获取已还账单平台、银行、产品信息
        $bills = UserBillFactory::fetchHomeBillsAndPlatformInfo($already);
        //求出账天数
        $already = UserBillStrategy::getAlreadyOrder($bills);
        //对已还信用卡数据进行排序处理
        $orderAlready = UserBillStrategy::getAlreadyMultisort($already);
        //SLogger::getStream()->info('出账',['data'=>$already]);
        //合并数组
        $datas = array_merge($waitBills, $orderAlready);
        //获取已还账单平台、银行、产品信息
        $datas = UserBillFactory::fetchHomeBillsAndPlatformInfo($datas);
        //分页
        $datas = DateUtils::pageInfo($datas, $data['pageSize'], $data['pageNum']);
        //处理状态值
        $bills = UserBillStrategy::getBillsStatus($datas['list']);
        //dd($bills);
        //已还账单数据处理，获取天数
        $bills = UserBillStrategy::getHomeBills($bills, $data);

        $res['list'] = $bills;
        $res['pageCount'] = $datas['pageCount'];

        return RestResponseFactory::ok($res);
    }

    /**
     * 首页网贷列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchHomeProducts(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        $data['deleteIds'] = $request->input('deleteIds', '');

        //在本月的、未还的网贷账单
        //手动输入 平台ids
        $data['bank_is_import'] = 0;
        $data['is_hidden'] = 0;
        //默认是网贷
        $data['billType'] = 2;
        $inputPlatformIds = UserBillPlatformFactory::fetchBillPlatformIdsByUserId($data);
        //平台id对应的账单id
        $inputBillIds = UserBillFactory::fetchRelBillIdsByPlatformIds($inputPlatformIds);
        //符合条件的账单ids
        $billIds = UserBillFactory::fetchInputBillIdsByBillIds($inputBillIds);
        //删除排序
        if (!empty($data['deleteIds'])) {
            $deleteIds = explode(',', $data['deleteIds']);
            $billIds = array_merge($deleteIds, $billIds);
        }
        //去重
        $data['billIds'] = array_unique($billIds);

        if (empty($data['billIds'])) {
            //暂无数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //获取网贷账单列表信息
        $datas = UserBillFactory::fetchHomeProductBills($data);
        if (!$datas['list']) {
            //暂无数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $pageCount = $datas['pageCount'];

        $datas = UserBillFactory::fetchHomeBillsAndPlatformInfo($datas['list']);
        //处理状态值
        $bills = UserBillStrategy::getBillsStatus($datas);
        //dd($bills);
        //已还账单数据处理，获取天数
        $bills = UserBillStrategy::getHomeBills($bills, $data);

        $res['list'] = $bills;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }


    /**
     * 修改账单状态 设为已还 【信用卡、网贷】
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBillStatus(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['billId'] = $request->input('billId');
        $data['billStatus'] = $request->input('billStatus', 1);

        $bill = UserBillFactory::fetchBillInfoById($data['billId']);
        //该账单不存在
        if (!$bill) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2305), 2305);
        }
        //平台id
        $bill['bill_platform_id'] = UserBillFactory::fetchRelPlatformIdByBillId($data['billId']);
        //账单已还，不可再次修改!
        if ($bill['bill_status'] == 1) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2304), 2304);
        }
        $bill['userId'] = $data['userId'];
        $bill['billId'] = $data['billId'];
        $bill['bill_status'] = 1;
        $bill['bill_cycle'] = $bill['bank_bill_cycle'];
        $bill['bill_time'] = $bill['bank_bill_time'];

        //账单状态设为已还 先建流水
        $log = UserBillFactory::createUserBillLog($bill);
        //修改账单状态
        $status = UserBillFactory::updateBillStatusById($bill);
        //请刷新
        if (!$log || !$status) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 首页账单笔数统计、负债金额统计 【包含隐藏账单项】
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCreditcardCount(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        // 账单类型 0全部， 1信用卡 ，2网贷
        $data['billType'] = $request->input('billType', 0);
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);

        //列表
        //手动输入 平台ids
        $data['bank_is_import'] = 0;
        $inputPlatformIds = UserBillPlatformFactory::fetchNotHiddenBillPlatformIdsByUserId($data);
        //平台id对应的账单id
        $inputBillIds = UserBillFactory::fetchRelBillIdsByPlatformIds($inputPlatformIds);
        //符合条件的账单ids
        $inputBillIds = UserBillFactory::fetchInputBillIdsByBillIds($inputBillIds);

        //网贷
        if ($data['billType'] == 2) {
            $data['billIds'] = $inputBillIds;
        } else {
            //导入 平台ids
            $data['bank_is_import'] = 1;
            $data['is_hidden'] = 0;
            $importPlatformIds = UserBillPlatformFactory::fetchNotHiddenBillPlatformIdsByUserId($data);
            //导入的平台下对应的相应的最新的账单id
            $importBillIds = UserBillFactory::fetchImportBillIdsByPlatformIds($importPlatformIds);
            //合并符合的账单id
            $billIds = array_merge($inputBillIds, $importBillIds);
            //去重
            $data['billIds'] = array_unique($billIds);
        }

        //求列表数据
        $bills = UserBillFactory::fetchHomeBills($data);

        $res['waitBillCount'] = isset($bills['waitBillCount']) ? $bills['waitBillCount'] : 0;
        $res['waitBillMoneyTotal'] = isset($bills['waitBillMoneyTotal']) ? DateUtils::formatDataToBillion($bills['waitBillMoneyTotal']) : '0.00';

        return RestResponseFactory::ok($res);
    }

    /**
     * 修改账单金额 【适用于信用卡、网贷】
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProductBillMoney(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['billId'] = $request->input('billId');
        $data['bill_money'] = $request->input('billMoney');

        $bill = UserBillFactory::fetchBillInfoById($data['billId']);
        //该账单不存在
        if (!$bill) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2305), 2305);
        }
        //平台id
        $bill['bill_platform_id'] = UserBillFactory::fetchRelPlatformIdByBillId($data['billId']);
        //账单已还，不可再次修改!
        if ($bill['bill_status'] == 1) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2304), 2304);
        }

        //修改账单金额
        $res = UserBillFactory::updateProductBillMoneyById($data);
        if (!$res) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2305), 2305);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 负债分析图表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function fetchBillAnalysis(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        //$userId = 1782;
        $analysisType = $request->input('analysisType', 1);

        $billStatistics = [];
        //负债分布 饼状图
        if ($analysisType == 1) {
            //根据用户id查用户下的、未删除的、本月份的、已统计完的平台负债信息
            $billStatistics = UserBillFactory::fetchMonthBillStatisticsByUserId($userId);

            if ($billStatistics) {
                //数据处理
                $billStatistics = UserBillStrategy::getMonthBillStatistics($billStatistics);
                //百分数数据处理
                $billStatistics = UserBillStrategy::getPercentBillStatistics($billStatistics);
            } else {
                $billStatistics['total_debts'] = 0;
                $billStatistics['list'] = [];
            }

        } elseif ($analysisType == 2) {
            //负债预估 折线图 12个月的 包含本月的前9个月，后3个月
            $billStatistics = UserBillFactory::fetchYearBillStatisticsByUserId($userId);
            //月份区间
            $regions = UserBillFactory::fetchMonthRegionBillStatistics();

            if ($billStatistics) {
                //梳理数据格式，将年份作为key值
                $billStatistics = UserBillStrategy::getYearBillStatistics($billStatistics);
            }

            //保证12个月月份存在
            $billStatistics = UserBillStrategy::getBillStatisticsYear($billStatistics, $regions);
            //转化格式
            $billStatistics = UserBillStrategy::getLineChartBillData($billStatistics);
        }

        //SLogger::getStream()->info('负债分析结果', ['data' => $billStatistics]);
        return RestResponseFactory::ok($billStatistics);
    }

}