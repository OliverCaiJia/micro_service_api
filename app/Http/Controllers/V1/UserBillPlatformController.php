<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserBillPlatformConstant;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\UserBill\Product\DoProductHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;
use App\Strategies\UserBillPlatformStrategy;
use App\Strategies\UserBillStrategy;
use Illuminate\Http\Request;

/**
 * 用户账单平台相关
 * Class UserBillController
 * @package App\Http\Controllers\V1
 */
class UserBillPlatformController extends Controller
{
    /**
     * 添加信用卡银行列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBillBanks()
    {
        //账单银行唯一标识
        $typeNid = UserBillPlatformConstant::BILL_PLATFORM_BANKS;
        //账单银行类型id
        $typeId = UserBillPlatformFactory::fetchBillBankTypeIdByNid($typeNid);
        //银行列表
        $banks = UserBillPlatformFactory::fetchBanksByTypeId($typeId);
        //暂无数据
        if (!$banks || !$typeId) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $banks = UserBillPlatformStrategy::getBillPlatformBanks($banks);

        return RestResponseFactory::ok($banks);
    }

    /**
     * 修改前查询信用卡信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCreditcardInfo(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['creditcardId'] = $request->input('creditcardId');

        //未删除的、手动添加的、信用卡信息
        $info = UserBillPlatformFactory::fetchCreditcardInfoById($data);
        if (!$info) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2302), 2302);
        }

        //账单银行唯一标识
        $typeNid = UserBillPlatformConstant::BILL_PLATFORM_BANKS;
        //账单银行类型id
        $bank['typeId'] = UserBillPlatformFactory::fetchBillBankTypeIdByNid($typeNid);
        $bank['bank_conf_id'] = $info['bank_conf_id'];
        //获取银行信息
        $bankinfo = UserBillPlatformFactory::fetchBankInfoById($bank);
        //添加银行信息数据
        $info['bank_short_name'] = $bankinfo ? $bankinfo['bank_short_name'] : '';

        return RestResponseFactory::ok($info);
    }

    /**
     * 创建或修改信用卡
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrUpdateCreditcard(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        $data['creditcardId'] = $request->input('creditcardId', '');

        //最大添加15张信用卡
        if (empty($data['creditcardId'])) {
            $count = UserBillPlatformFactory::fetchCreditcardCount($data['userId']);
            if ($count >= UserBillPlatformConstant::BANK_CREDITCARD_COUNT) {
                //最多只能添加15张，您可以尝试删除不常用的信用卡
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2308), 2308);
            }
        }
        //记流水
        $log = UserBillPlatformFactory::createCreditcardLog($data);
        //创建或修改账单表
        $update = UserBillPlatformFactory::createOrUpdateCreditcard($data);
        if (!$log || !$update) {
            //出错了,请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }
        //数据处理  返回银行log&信用卡平台id
        //账单银行唯一标识
        $typeNid = UserBillPlatformConstant::BILL_PLATFORM_BANKS;
        //账单银行类型id
        $bank['typeId'] = UserBillPlatformFactory::fetchBillBankTypeIdByNid($typeNid);
        $bank['bank_conf_id'] = $update['bank_conf_id'];
        //获取银行信息
        $params['bankinfo'] = UserBillPlatformFactory::fetchBankInfoById($bank);
        $params['update'] = $update;
        $update = UserBillPlatformStrategy::getUpdateCreditcardInfo($params);

        return RestResponseFactory::ok($update);
    }

    /**
     *
     * 判断是否可以添加信用卡&导入信用卡数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCreditcardSign(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        //信用卡平台总数据
        $count = UserBillPlatformFactory::fetchCreditcardCount($userId);
        //默认可以添加
        $res['add_creditcard_sign'] = 1;
        //大于15张 不允许继续添加
        if ($count >= UserBillPlatformConstant::BANK_CREDITCARD_COUNT) {
            $res['add_creditcard_sign'] = 0;
        }

        //查询任务是否过期
        //在有效期内的账单任务
        $taskCount = UserBillPlatformFactory::fetchBillTaskCount($userId);

        //默认可以查询
        $res['task_sign'] = 1;
        $res['task_meg'] = '';
        if ($taskCount && $taskCount >= UserBillPlatformConstant::BILL_IMPORT_DAY_COUNT) {
            //不可以查询 @todo 上线之前修改为0
            $res['task_sign'] = 0;
            $res['task_meg'] = RestUtils::getErrorMessage(2309);
        }

        return RestResponseFactory::ok($res);
    }

    /**
     * 信用卡平台列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCreditcards(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        //信用卡平台滑动列表数据 15张 不需要分页
        $creditcards = UserBillPlatformFactory::fetchCreditcards($userId);

        if (!$creditcards) {
            //暂无数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //手动输入：需要未还账单信息，没有未还显示--
        //导入：需要未还、已还账单信息，状态与首页列表一致
        $creditcards = UserBillPlatformFactory::fetchCreditcardPlatformAndBillInfo($creditcards);

        $creditcards = UserBillStrategy::getBillsStatus($creditcards);
        //展示数据处理
        $creditcards = UserBillPlatformStrategy::getCreditcards($creditcards);

        return RestResponseFactory::ok($creditcards);
    }

    /**
     * 删除账单平台
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBillPlatform(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['billPlatformId'] = $request->input('billPlatformId');

        //删除账单平台
        $delete = UserBillPlatformFactory::deleteBillPlatformById($data);
        if (!$delete) {
            //出错了，请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 修改账单平台状态 还款提醒&隐藏
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['billPlatformId'] = $request->input('billPlatformId');
        $data['alertStatus'] = $request->input('alertStatus');
        $data['hiddenStatus'] = $request->input('hiddenStatus');

        //修改账单平台还款提醒状态
        $delete = UserBillPlatformFactory::updateStatus($data);
        if (!$delete) {
            //出错了，请刷新重试
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 添加网贷产品列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProducts()
    {
        //添加网贷产品列表
        $products = UserBillPlatformConstant::BILL_PLATFORM_PRODUCTS_RENEW;

        return RestResponseFactory::ok($products);
    }

    /**
     * 添加或修改网贷平台数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrUpdateProduct(Request $request)
    {
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;
        //修改网贷平台id
        $data['billProductId'] = $request->input('billProductId', '');
        //网贷
        $data['bill_platform_type'] = 2;
        //金额
        $data['bill_money'] = $data['billMoney'];

        $product = new DoProductHandler($data);
        $re = $product->handleRequest();

        //错误提示
        if (isset($re['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $re['error'], $re['code'], $re['error']);
        }

        return RestResponseFactory::ok($re);
    }

    /**
     * 单个网贷产品信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProduct(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['billProductId'] = $request->input('billProductId');

        //修改前展示网贷平台信息
        $product = UserBillPlatformFactory::fetchProduct($data);
        //暂无数据
        if (!$product) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //获取当前期数
        //网贷账单id
        $data['billIds'] = UserBillFactory::fetchRelBillIdsById($product['id']);
        //当前还款日对应的月份
        $billinfo = UserBillFactory::fetchRecentPeriodNumBill($data);
        if (!$billinfo) {
            //直接返回最后一个账单信息
            $billinfo = UserBillFactory::fetchLastProductBillinfo($data);
        }

        $product['update_sign'] = 1;
        //修改时验证：超过最后还款月份不可进行修改
        //查询未删除、最晚还款时间
        $repayTime = UserBillFactory::fetchProductRepayTimeByBillIds($data['billIds']);
        //当前时间
        $now = date('Y-m-d', time());
        //当前时间>最晚还款时间、不可以进行修改
        if (strtotime($now) > strtotime($repayTime)) {
            $product['update_sign'] = 0;
        }
        //全部修改为已还 也不可进行修改
        $alreadyCount = UserBillFactory::fetchBillsAlreadyCount($data['billIds']);
        if ($alreadyCount == $product['product_period_total']) {
            $product['update_sign'] = 0;
        }

        $product['product_bill_period_num'] = isset($billinfo['product_bill_period_num']) ? $billinfo['product_bill_period_num'] : 1;
        $product['bill_money'] = isset($billinfo['bill_money']) ? $billinfo['bill_money'] : '0.00';

        return RestResponseFactory::ok($product);
    }

    /**
     * 网贷产品详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductInfo(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['billProductId'] = $request->input('billProductId');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);

        //网贷借款时间
        $product = UserBillPlatformFactory::fetchPlatformInfoById($data['billProductId']);
        //暂无数据
        if (!$product) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //账单信息
        $data['billIds'] = UserBillFactory::fetchRelBillIdsById($product['id']);

        //账单信息
        $billInfos = UserBillFactory::fetchProductBillInfosById($data);
        if (!$billInfos['list']) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //总页数
        $pageCount = $billInfos['pageCount'];
        //数据处理
        $info = UserBillPlatformStrategy::getProductInfo($billInfos['list'], $product);

        $res['list'] = $info;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }

    /**
     * 网贷详情页统计
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProductInfoCount(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['billProductId'] = $request->input('billProductId');

        //网贷借款时间
        $product = UserBillPlatformFactory::fetchPlatformInfoById($data['billProductId']);
        //暂无数据
        if (!$product) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //账单信息
        $data['billIds'] = UserBillFactory::fetchRelBillIdsById($product['id']);

        //求已还总金额
        $wait['billIds'] = $data['billIds'];
        $wait['money_sign'] = 1;
        $already_money = UserBillFactory::fetchProductBillMoneyById($wait);

        //全部贷款金额
        $wait['money_sign'] = 0;
        $total_money = UserBillFactory::fetchProductBillMoneyById($wait);

        //待还总金额
        $wait_money = bcsub($total_money, $already_money, 2);

        //最低还款日期
        $min_repay_time = UserBillFactory::fetchMinProductRepayTimeByIds($wait['billIds']);
        $loan_time = date('Y-m-d', strtotime("$min_repay_time -1 month"));

        $res['already_money'] = empty($already_money) ? '0.00' : DateUtils::formatDataToBillion($already_money);
        $res['total_money'] = empty($total_money) ? '0.00' : DateUtils::formatDataToBillion($total_money);
        $res['wait_money'] = empty($wait_money) ? '0.00' : DateUtils::formatDataToBillion($wait_money);
        $res['created_at'] = DateUtils::formatTimeToYmdBySpot($loan_time);
        $res['product_id'] = $product['product_id'];
        $res['product_name'] = $product['product_name'];

        return RestResponseFactory::ok($res);
    }

    /**
     * 账单管理 —— 网贷列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchManageProducts(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);

        //所有网贷平台信息
        $data['bill_platform_type'] = 2;
        $products = UserBillPlatformFactory::fetchManages($data);
        if (!$products['list']) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        $pageCount = $products['pageCount'];
        //获取每个网贷产品借款总额度
        $products = UserBillPlatformFactory::fetchProductTotalMoney($products['list']);
        //数据处理
        $products = UserBillPlatformStrategy::getManageProducts($products);

        $res['list'] = $products;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }

    /**
     * 账单管理 —— 信用卡列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCreditcardManages(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);

        //用户下的、未删除的所有的信用卡
        $data['bill_platform_type'] = 1;
        $creditcards = UserBillPlatformFactory::fetchManages($data);
        if (!$creditcards['list']) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        $pageCount = $creditcards['pageCount'];
        //手动添加显示当月账单日、还款日；导入根据最近一期状态展示
        $creditcards = UserBillPlatformFactory::fetchCreditcardPlatformAndBillInfo($creditcards['list']);
        //信用卡账单状态
        $creditcards = UserBillStrategy::getBillsStatus($creditcards);
        //账单管理展示数据处理
        $creditcards = UserBillPlatformStrategy::getCreditcardManages($creditcards);

        $res['list'] = $creditcards;
        $res['pageCount'] = $pageCount;

        return RestResponseFactory::ok($res);
    }


}
