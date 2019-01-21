<?php

namespace App\Models\Factory;

use App\Constants\UserBillPlatformConstant;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserBill;
use App\Models\Orm\UserBillBankConf;
use App\Models\Orm\UserBillBankType;
use App\Models\Orm\UserBillCount;
use App\Models\Orm\UserBillDetails;
use App\Models\Orm\UserBillLog;
use App\Models\Orm\UserBillMail;
use App\Models\Orm\UserBillPlatform;
use App\Models\Orm\UserBillPlatformBillRel;
use App\Models\Orm\UserBillPlatformLog;
use App\Services\Core\Store\Qiniu\QiniuService;
use Illuminate\Support\Facades\DB;

/**
 * 用户账单相关工厂类
 * Class UserBillFactory
 * @package App\Models\Factory
 */
class UserBillFactory extends AbsModelFactory
{
    /**
     * 根据账单id 获取账单日期
     * @param array $billIds
     * @return array
     */
    public static function fetchBankBillTimes($billIds = [])
    {
        $billTimes = UserBill::select(DB::raw("date_format(bank_bill_time,'%Y-%m') as bank_bill_time "))
            ->whereIn('id', $billIds)
            ->pluck('bank_bill_time')->toArray();

        return $billTimes ? $billTimes : [];
    }

    /**
     * 账单的还款日期集合
     * @param array $billIds
     * @return array
     */
    public static function fetchRepayBillTimes($billIds = [])
    {
        $repayTimes = UserBill::select(['id', 'repay_time'])
            ->whereIn('id', $billIds)
            ->pluck('repay_time')->toArray();

        return $repayTimes ? $repayTimes : [];
    }

    /**
     * 创建账单流水
     * @bill_type '网贷或信用卡类型 (1信用卡, 2网贷)',
     * @is_import '导入类型 (0手动, 1魔蝎导入)',
     * @is_delete '是否删除(0未删除, 1已删除)',
     * @is_hidden '是否隐藏(0未隐藏, 1已隐藏)',
     * @param array $params
     * @return bool
     */
    public static function createUserBillLog($params = [])
    {
        $log = new UserBillLog();
        $log->user_id = $params['userId'];
        $log->bill_platform_id = $params['bill_platform_id'];
        $log->bank_bill_details = isset($params['bank_bill_details']) ? $params['bank_bill_details'] : '';
        $log->bank_bill_cycle = isset($params['bill_cycle']) ? $params['bill_cycle'] : '';
        $log->bank_bill_time = isset($params['bill_time']) ? $params['bill_time'] : '';
        $log->product_bill_period_num = isset($params['product_bill_period_num']) ? $params['product_bill_period_num'] : 0;
        $log->repay_time = isset($params['repay_time']) ? $params['repay_time'] : '';
        $log->bill_money = isset($params['bill_money']) ? $params['bill_money'] : 0;
        $log->bill_status = isset($params['bill_status']) ? $params['bill_status'] : 0;
        $log->pour_bill_id = isset($params['pour_bill_id']) ? $params['pour_bill_id'] : '';
        $log->bill_type = isset($params['bill_type']) ? $params['bill_type'] : 1;
        $log->is_import = isset($params['is_import']) ? $params['is_import'] : 0;
        $log->is_delete = isset($params['is_delete']) ? $params['is_delete'] : 0;
        $log->is_hidden = isset($params['is_hidden']) ? $params['is_hidden'] : 0;
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();
        return $log->save();
    }

    /**
     * 根据账单id获取账单信息
     * @param string $billId
     * @return array
     */
    public static function fetchBillInfoById($billId = '')
    {
        $info = UserBill::select(['id', 'bank_bill_cycle', 'bank_bill_time', 'product_bill_period_num', 'repay_time', 'bill_money', 'bill_status'])
            ->where(['id' => $billId])
            ->first();

        return $info ? $info->toArray() : [];
    }

    /**
     * 添加或修改信用卡账单 并返回账单id
     * @param array $params
     * @return mixed
     */
    public static function createOrUpdateUserBill($params = [])
    {
        $billId = isset($params['billId']) ? $params['billId'] : 0;
        $bill = UserBill::where(['id' => $billId])
            ->first();

        if (!$bill) {
            $bill = new UserBill();
            $bill->created_at = date('Y-m-d H:i:s', time());
            $bill->created_ip = Utils::ipAddress();
        }

        $bill->user_id = $params['userId'];
        $bill->bank_bill_cycle = isset($params['bill_cycle']) ? $params['bill_cycle'] : '';
        $bill->bank_bill_time = isset($params['bill_time']) ? $params['bill_time'] : '';
        $bill->product_bill_period_num = isset($params['product_bill_period_num']) ? $params['product_bill_period_num'] : 0;
        $bill->repay_time = isset($params['repay_time']) ? $params['repay_time'] : '';
        $bill->bill_money = isset($params['bill_money']) ? $params['bill_money'] : '';
        $bill->bill_status = isset($params['new_bill_status']) ? $params['new_bill_status'] : 0;
        $bill->bill_type = isset($params['bill_type']) ? $params['bill_type'] : 1;
        $bill->is_import = isset($params['is_import']) ? $params['is_import'] : 0;
        $bill->is_delete = isset($params['is_delete']) ? $params['is_delete'] : 0;
        $bill->is_hidden = isset($params['is_hidden']) ? $params['is_hidden'] : 0;
        $bill->updated_at = date('Y-m-d H:i:s', time());
        $bill->updated_ip = Utils::ipAddress();
        $res = $bill->save();

        return $res ? $bill->id : '';
    }

    /**
     * 总期数&每期数都不变  不需要改变账单还款状态
     * @param array $params
     * @return mixed|string
     */
    public static function updateUserBill($params = [])
    {
        $billId = isset($params['billId']) ? $params['billId'] : 0;
        $bill = UserBill::where(['id' => $billId])
            ->first();

        if (!$bill) {
            $bill = new UserBill();
            $bill->created_at = date('Y-m-d H:i:s', time());
            $bill->created_ip = Utils::ipAddress();
        }

        $bill->user_id = $params['userId'];
        $bill->bank_bill_cycle = isset($params['bill_cycle']) ? $params['bill_cycle'] : '';
        $bill->bank_bill_time = isset($params['bill_time']) ? $params['bill_time'] : '';
        $bill->product_bill_period_num = isset($params['product_bill_period_num']) ? $params['product_bill_period_num'] : 0;
        $bill->repay_time = isset($params['repay_time']) ? $params['repay_time'] : '';
        $bill->bill_money = isset($params['bill_money']) ? $params['bill_money'] : '';
        //$bill->bill_status = isset($params['new_bill_status']) ? $params['new_bill_status'] : 0;
        $bill->bill_type = isset($params['bill_type']) ? $params['bill_type'] : 1;
        $bill->is_import = isset($params['is_import']) ? $params['is_import'] : 0;
        $bill->is_delete = isset($params['is_delete']) ? $params['is_delete'] : 0;
        $bill->is_hidden = isset($params['is_hidden']) ? $params['is_hidden'] : 0;
        $bill->updated_at = date('Y-m-d H:i:s', time());
        $bill->updated_ip = Utils::ipAddress();
        $res = $bill->save();

        return $res ? $bill->id : '';
    }

    /**
     * 创建或查询账单与账单平台关联表
     * @param array $params
     * @return mixed
     */
    public static function createUserBillRel($params = [])
    {
        $rel = UserBillPlatformBillRel::select(['id'])
            ->where(['bill_id' => $params['bill_id'], 'bill_platform_id' => $params['bill_platform_id']])
            ->first();

        if (empty($rel)) {
            $rel = new UserBillPlatformBillRel();
            $rel->created_at = date('Y-m-d H:i:s', time());
            $rel->created_ip = Utils::ipAddress();
        }

        $rel->bill_id = $params['bill_id'];
        $rel->bill_platform_id = $params['bill_platform_id'];
        $rel->updated_at = date('Y-m-d H:i:s', time());
        $rel->updated_ip = Utils::ipAddress();

        return $rel->save();
    }

    /**
     * 根据账单平台id获取账单ids
     * @param string $billPlatformId
     * @return array
     */
    public static function fetchRelBillIdsById($billPlatformId = '')
    {
        $billIds = UserBillPlatformBillRel::select(['bill_id'])
            ->where(['bill_platform_id' => $billPlatformId])
            ->pluck('bill_id')
            ->toArray();

        return $billIds ? $billIds : [];
    }

    /**
     * 未删除的总id数
     * @param array $billIds
     * @return array
     */
    public static function fetchBillsNotDelete($billIds = [])
    {
        $billIds = UserBill::select(['id'])
            ->whereIn('id', $billIds)
            ->where(['is_delete' => 0])
            ->pluck('id')
            ->toArray();

        return $billIds ? $billIds : [];
    }

    /**
     * 导入的平台下对应的相应的最新的账单id
     * @param array $importPlatformIds
     * @return array
     */
    public static function fetchImportBillIdsByPlatformIds($importPlatformIds = [])
    {
        $res = [];
        foreach ($importPlatformIds as $key => $value) {
            //平台id对应的账单id
            $importBillIds = UserBillFactory::fetchRelBillIdsById($value);
            //符合条件的账单ids 导入保留最新的一个账单
            $billId = UserBillFactory::fetchImportBillIdsByBillIds($importBillIds);
            array_push($res, $billId);
        }
        return $res ? $res : [];
    }

    /**
     * 所有平台ids对应的账单ids
     * @param array $billPlatformIds
     * @return array
     */
    public static function fetchRelBillIdsByPlatformIds($billPlatformIds = [])
    {
        $billIds = UserBillPlatformBillRel::select(['bill_id'])
            ->whereIn('bill_platform_id', $billPlatformIds)
            ->pluck('bill_id')
            ->toArray();

        return $billIds ? $billIds : [];
    }

    /**
     * 在关联表 根据账单id查平台id
     * @param string $billId
     * @return string
     */
    public static function fetchRelPlatformIdByBillId($billId = '')
    {
        $platformId = UserBillPlatformBillRel::select('bill_platform_id')
            ->where(['bill_id' => $billId])
            ->first();

        return $platformId ? $platformId->bill_platform_id : '';
    }

    /**
     * 获取某平台下账单列表
     * @is_delete '是否删除(0未删除, 1已删除)',
     * @is_hidden '是否隐藏(0未隐藏, 1已隐藏)',
     * @param array $params
     * @return array
     */
    public static function fetchCreditcardBills($params = [])
    {
        $pageSize = intval($params['pageSize']);
        $pageNum = intval($params['pageNum']);

        $query = UserBill::select(['id', 'bank_bill_cycle', 'bank_bill_time', 'product_bill_period_num', 'repay_time', 'bill_money', 'bill_status', 'is_import'])
            ->where(['is_delete' => 0, 'is_hidden' => 0])
            ->whereIn('id', $params['billIds']);

        //排序 账单时间倒叙排列
        $query->orderBy('bank_bill_time', 'desc');

        //分页
        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $userBills = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $res['list'] = $userBills;
        $res['pageCount'] = $countPage ? $countPage : 0;

        return $res ? $res : [];
    }

    /**
     * 账单明细内置内容分页
     * @param array $params
     * @return string
     */
    public static function fetchCreditcardBillDetails($params = [])
    {
        $query = UserBillDetails::select(['id', 'bill_details'])
            ->where(['bill_id' => $params['billId']]);

        $detail = $query->first();

        return $detail ? $detail->bill_details : '';
    }

    /**
     * 导入结果页  最新账单数据
     * @param array $params
     * @return array
     */
    public static function fetchNewestBillsByLimit($params = [])
    {
        $newestBills = UserBill::select(['id', 'bank_bill_time', 'bill_money'])
            ->whereIn('id', $params['newestBillIds'])
            ->orderBy('bank_bill_time', 'desc')
            ->limit($params['bank_new_bills_count'])
            ->get()
            ->toArray();

        return $newestBills ? $newestBills : [];
    }

    /**
     * 当月之前待还账单总笔数
     * @param array $billIds
     * @return array
     */
    public static function fetchWaitBillByBillId($billIds = [])
    {
        //当月月底
        $month = date('Y-m-t', time());

        $query = UserBill::select(['id', 'bank_bill_time', 'bill_money'])
            ->whereIn('id', $billIds)
            ->where('bill_status', '!=', 1)
            ->where('bank_bill_time', '<=', $month);
        SLogger::getStream()->info('数据', ['query' => $query->get()->toArray()]);

        $res['count'] = $query->count();
        $res['bill_money_total'] = $query->sum('bill_money');

        return $res ? $res : [];
    }

    /**
     * 手动输入、符合筛选条件的账单ids
     * @bill_status 还款账单状态(0待还, 1已还, 2未还)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @param array $billIds
     * @return array
     */
    public static function fetchInputBillIdsByBillIds($billIds = [])
    {
        //当月月底
        $month = date('Y-m-t', time());
        //当前时间大于账单日才会被显示出来
        $show = date('Y-m-d', time());

        $query = UserBill::select(['id', 'bank_bill_time', 'bill_money'])
            ->whereIn('id', $billIds)
            ->where(['is_delete' => 0])
            ->where('bill_status', '!=', 1)
            ->where('bank_bill_time', '<=', $show)
            ->where('bank_bill_time', '<=', $month);

        $ids = $query->pluck('id')->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 取账单时间最近的一个账单id
     * @bill_status 还款账单状态(0待还, 1已还, 2未还)
     * @param array $billIds
     * @return string
     */
    public static function fetchImportBillIdsByBillIds($billIds = [])
    {
        $now = date('Y-m-d', time());

        $query = UserBill::select(['id', 'bank_bill_time', 'bill_money'])
            ->whereIn('id', $billIds)
            ->orderBy('bank_bill_time', 'desc')
            ->where('bank_bill_time', '<=', $now)
            ->limit(1);

        $ids = $query->first();

        return $ids ? $ids->id : '';
    }

    /**
     * 取账单时间最近的一个账单信息
     * @param array $billIds
     * @return array
     */
    public static function fetchNearestBill($billIds = [])
    {
        //不超过当前时间
        $now = date('Y-m-d', time());

        $query = UserBill::select(['id', 'bank_bill_time', 'bill_money'])
            ->whereIn('id', $billIds)
            ->orderBy('bank_bill_time', 'desc')
            ->where('bank_bill_time', '<=', $now)
            ->orderBy('id', 'desc')
            ->limit(1);

        $bill = $query->first();

        return $bill ? $bill->toArray() : [];
    }

    /**
     * 首页列表数据
     * @param array $params
     * @return array
     */
    public static function fetchWaitBills($params = [])
    {
        //月底
        $now = date('Y-m-t', time());

        $query = UserBill::select(['id', 'bank_bill_time', 'bill_money', 'bank_bill_cycle', 'product_bill_period_num', 'repay_time', 'bill_status'])
            ->whereIn('id', $params['billIds'])
            ->where('bank_bill_time', '<=', $now);

        //排序
        //未还账单
        $query->where('bill_status', '!=', 1);
        // 账单时间正序
        $query->orderBy('repay_time', 'asc')->orderBy('id', 'desc');

        $waitBills = $query->get()->toArray();

        return $waitBills ? $waitBills : [];
    }

    /**
     * 首页网贷列表数据处理
     * @param array $params
     * @return array
     */
    public static function fetchHomeBills($params = [])
    {
        $pageSize = intval($params['pageSize']);
        $pageNum = intval($params['pageNum']);
        //月底
        $now = date('Y-m-t', time());
        $query = UserBill::select(['id', 'bank_bill_time', 'bill_money', 'bank_bill_cycle', 'product_bill_period_num', 'repay_time', 'bill_status'])
            ->whereIn('id', $params['billIds'])
            ->where('bill_status', '!=', 1)
            ->where('bank_bill_time', '<=', $now);
        //负债笔数
        $waitBillCount = $query->count();
        //负债总价钱
        $waitBillMoney = $query->sum('bill_money');
        //排序
        // 未还在前已还在后
        $query->orderBy('bill_status', 'asc');
        // 账单时间正序
        $query->orderBy('bank_bill_time', 'asc')->orderBy('id', 'desc');

        //分页
        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */
        $bills = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $res['list'] = $bills;
        $res['pageCount'] = $countPage ? $countPage : 0;
        $res['waitBillCount'] = empty($waitBillCount) ? 0 : $waitBillCount;
        $res['waitBillMoneyTotal'] = empty($waitBillMoney) ? '0.00' : $waitBillMoney;
        return $res ? $res : [];
    }

    /**
     * 首页信用卡列表 —— 已还账单
     * @param array $params
     * @return array
     */
    public static function fetchAlreadyCreditcardBills($params = [])
    {
        //月底
        $now = date('Y-m-t', time());

        $query = UserBill::select(['id', 'bank_bill_time', 'bill_money', 'bank_bill_cycle', 'product_bill_period_num', 'repay_time', 'bill_status'])
            ->whereIn('id', $params['billIds'])
            ->where('bank_bill_time', '<=', $now);

        //排序
        //已还账单
        $query->where('bill_status', '=', 1);
        // 账单时间正序
        $query->orderBy('repay_time', 'asc')->orderBy('id', 'desc');

        $already = $query->get()->toArray();

        return $already ? $already : [];
    }

    /**
     * 获取相应账单平台信息
     * @param array $bills
     * @return array
     */
    public static function fetchHomeBillsAndPlatformInfo($bills = [])
    {
        foreach ($bills as $key => $val) {
            //取平台id
            $platformId = UserBillFactory::fetchRelPlatformIdByBillId($val['id']);
            //平台信息
            $platformInfo = UserBillPlatformFactory::fetchPlatformInfoById($platformId);
            $bills[$key]['platformInfo'] = $platformInfo;

            //当前账单日
            $bank_bill_time = $val['bank_bill_time'];
            $strto_bank_bill_time = strtotime($bank_bill_time);
            //下一个账单日
            $next_bill_time = date('Y-m-d', strtotime("$bank_bill_time +1 month"));
            $data['strto_bank_bill_time'] = $strto_bank_bill_time;
            $data['strto_next_bill_time'] = strtotime($next_bill_time);
            $bills[$key]['billCount'] = UserBillFactory::fetchIsWithin($data);

            //银行信息
            //账单银行唯一标识
            $typeNid = UserBillPlatformConstant::BILL_PLATFORM_BANKS;
            //账单银行类型id
            $typeId = UserBillPlatformFactory::fetchBillBankTypeIdByNid($typeNid);
            //银行列表
            $bank['bank_conf_id'] = $platformInfo['bank_conf_id'];
            $bank['typeId'] = $typeId;
            $banks = UserBillPlatformFactory::fetchBankInfoById($bank);

            $bills[$key]['bank_bill_date'] = $platformInfo['bank_bill_date'];
            $bills[$key]['bank_repay_day'] = $platformInfo['bank_repay_day'];
            $bills[$key]['bank_is_import'] = $platformInfo['bank_is_import'];
            $bills[$key]['billinfo_sign'] = empty($val) ? 0 : 1;
            $bills[$key]['bank_short_name'] = isset($banks['bank_short_name']) ? $banks['bank_short_name'] : '';
            $bills[$key]['bank_logo'] = isset($banks['bank_logo']) ? $banks['bank_logo'] : '';
            $bills[$key]['bank_watermark_link'] = isset($banks['bank_watermark_link']) ? $banks['bank_watermark_link'] : '';
        }

        return $bills ? $bills : [];
    }

    /**
     * 首页网贷列表
     * @param array $params
     * @return array
     */
    public static function fetchHomeProductBills($params = [])
    {
        $pageSize = intval($params['pageSize']);
        $pageNum = intval($params['pageNum']);
        //月底
        $now = date('Y-m-t', time());
        //当前时间大于账单日才会被显示出来
        $show = date('Y-m-d', time());

        $query = UserBill::select(['id', 'bank_bill_time', 'bill_money', 'bank_bill_cycle', 'product_bill_period_num', 'repay_time', 'bill_status'])
            ->whereIn('id', $params['billIds'])
            ->where('bank_bill_time', '<=', $show)
            ->where('bank_bill_time', '<=', $now);

        //排序
        //未还账单
        $query->where('bill_status', '!=', 1);
        // 账单时间正序
        $query->orderBy('repay_time', 'asc')->orderBy('id', 'desc');

        //分页
        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */
        $bills = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $res['list'] = $bills;
        $res['pageCount'] = $countPage ? $countPage : 0;

        return $res ? $res : [];
    }

    /**
     * 可进行魔蝎导入邮箱列表
     * @return array
     */
    public static function fetchImportBillMails()
    {
        $mails = UserBillMail::select(['id', 'name', 'image_link', 'type_nid'])
            ->where(['status' => 1])
            ->orderBy('sort', 'asc')
            ->get()
            ->toArray();

        return $mails ? $mails : [];
    }

    /**
     * 修改账单状态
     * @param array $params
     * @return mixed
     */
    public static function updateBillStatusById($params = [])
    {
        $status = UserBill::where(['user_id' => $params['userId'], 'id' => $params['billId']])
            ->update([
                'bill_status' => $params['bill_status'],
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);

        return $status;
    }

    /**
     * 当前时间是否在账单周期内
     * @param array $params
     * @return int
     */
    public static function fetchIsWithin($params = [])
    {
        $now = date('Y-m-d', time());

        $sign = 0;
        if (strtotime($now) >= $params['strto_bank_bill_time'] && strtotime($now) <= $params['strto_next_bill_time']) {
            $sign = 1;
        } else {
            $sign = 0;
        }

        return $sign;
    }

    /**
     * 最大还账单时间
     * @bill_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @param array $billIds
     * @return array
     */
    public static function fetchMaxBillTimeByBillIds($billIds = [])
    {
        $query = UserBill::select(['id', 'bank_bill_time'])
            ->whereIn('id', $billIds)
            ->orderBy('bank_bill_time', 'desc')
            ->where(['bill_type' => 1])
            ->limit(1)
            ->first();

        return $query ? $query->toArray() : [];
    }

    /**
     * 网贷账单、询未删除、最晚还款时间
     * @bill_type '网贷或信用卡类型 (1信用卡, 2网贷)',
     * @is_import '导入类型 (0手动, 1魔蝎导入)',
     * @is_delete '是否删除(0未删除, 1已删除)',
     * @param array $billIds
     * @return string
     */
    public static function fetchProductRepayTimeByBillIds($billIds = [])
    {
        $repayTime = UserBill::select(['id', 'repay_time'])
            ->whereIn('id', $billIds)
            ->where(['bill_type' => 2, 'is_import' => 0, 'is_delete' => 0])
            ->orderBy('repay_time', 'desc')
            ->limit(1)
            ->first();

        return $repayTime ? $repayTime->repay_time : '';
    }

    /**
     * 未删除已还总个数
     * @bill_type '网贷或信用卡类型 (1信用卡, 2网贷)',
     * @is_import '导入类型 (0手动, 1魔蝎导入)',
     * @is_delete '是否删除(0未删除, 1已删除)',
     * @param array $billIds
     * @return int
     */
    public static function fetchBillsAlreadyCount($billIds = [])
    {
        $count = UserBill::select(['id', 'repay_time'])
            ->whereIn('id', $billIds)
            ->where(['bill_type' => 2, 'is_import' => 0, 'bill_status' => 1, 'is_delete' => 0])
            ->count();

        return $count ? $count : 0;
    }

    /**
     * 获取本月的网贷的期数
     * @param array $data
     * @return array
     */
    public static function fetchRecentPeriodNumBill($data = [])
    {
        $res = UserBill::select(['id', 'repay_time', 'product_bill_period_num', 'bill_money'])
            ->where(['user_id' => $data['userId']])
            ->whereIn('id', $data['billIds'])
            ->where(['bill_type' => 2, 'is_import' => 0, 'is_delete' => 0])
            ->where('bill_status', '!=', 1)
            ->orderBy('repay_time', 'asc')
            ->limit(1)
            ->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 最后一个还款日对应账单信息
     * @param array $data
     * @return array
     */
    public static function fetchLastProductBillinfo($data = [])
    {
        $res = UserBill::select(['id', 'repay_time', 'product_bill_period_num', 'bill_money'])
            ->where(['user_id' => $data['userId']])
            ->whereIn('id', $data['billIds'])
            ->where(['bill_type' => 2, 'is_import' => 0, 'is_delete' => 0])
            ->orderBy('repay_time', 'desc')
            ->limit(1)
            ->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 单个产品平台对应的当前期数
     * @param array $params
     * @return array
     */
    public static function fetchProductById($params = [])
    {
        $query = UserBill::select(['id', 'product_bill_period_num', 'bill_money'])
            ->where(['repay_time' => $params['repay_time'], 'user_id' => $params['userId']])
            ->whereIn('id', $params['billIds'])
            ->limit(1);

        $info = $query->first();

        return $info ? $info->toArray() : [];
    }

    /**
     * 未还账单信息
     * @bill_status '还款账单状态(0待还, 1已还, 2未还)',
     * @bill_type '网贷或信用卡类型 (1信用卡, 2网贷)',
     * @is_import '导入类型 (0手动, 1魔蝎导入)',
     * @is_delete '是否删除(0未删除, 1已删除)',
     * @is_hidden '是否隐藏(0未隐藏, 1已隐藏)',
     * @param array $billIds
     * @return array
     */
    public static function fetchWaitBillInfoByBillId($billIds = [])
    {
        //月底
        $now = date('Y-m-d', time());
        $billInfo = UserBill::select(['id', 'bank_bill_time', 'repay_time', 'bill_money', 'bill_status'])
            ->where(['bill_type' => 1, 'is_import' => 0, 'is_delete' => 0, 'is_hidden' => 0])
            ->whereIn('id', $billIds)
            ->orderBy('bank_bill_time', 'desc')
            ->where('bank_bill_time', '<=', $now)
            ->limit(1)
            ->first();

        return $billInfo ? $billInfo->toArray() : [];
    }

    /**
     * 导入 最近一期账单信息
     * @bill_status '还款账单状态(0待还, 1已还, 2未还)',
     * @bill_type '网贷或信用卡类型 (1信用卡, 2网贷)',
     * @is_import '导入类型 (0手动, 1魔蝎导入)',
     * @is_delete '是否删除(0未删除, 1已删除)',
     * @is_hidden '是否隐藏(0未隐藏, 1已隐藏)',
     * @param array $billIds
     * @return array
     */
    public static function fetchImportBillInfoByBillId($billIds = [])
    {
        //月底
        $now = date('Y-m-d', time());
        $billInfo = UserBill::select(['id', 'bank_bill_time', 'repay_time', 'bill_money', 'bill_status'])
            ->where(['bill_type' => 1, 'is_import' => 1, 'is_delete' => 0, 'is_hidden' => 0])
            ->whereIn('id', $billIds)
            ->orderBy('bank_bill_time', 'desc')
            ->where('bank_bill_time', '<=', $now)
            ->limit(1)
            ->first();

        return $billInfo ? $billInfo->toArray() : [];
    }

    /**
     * 网贷详情账单id
     * @param array $params
     * @return array
     */
    public static function fetchProductBillInfosById($params = [])
    {
        $pageSize = intval($params['pageSize']);
        $pageNum = intval($params['pageNum']);

        $query = UserBill::select(['id', 'bank_bill_time', 'repay_time', 'bill_money', 'bill_status', 'product_bill_period_num', 'is_import'])
            ->where(['is_delete' => 0])
            ->whereIn('id', $params['billIds'])
            ->orderBy('repay_time', 'asc');

        //分页
        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $billInfos = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $res['list'] = $billInfos;
        $res['pageCount'] = $countPage ? $countPage : 0;

        return $res ? $res : [];
    }

    /**
     * 产品详情负债金额
     * @param array $params
     * @return string
     */
    public static function fetchProductBillMoneyById($params = [])
    {
        $query = UserBill::select(['id', 'bank_bill_time', 'repay_time', 'bill_money', 'bill_status', 'product_bill_period_num'])
            ->where(['is_delete' => 0])
            ->whereIn('id', $params['billIds']);

        //已还
        if ($params['money_sign'] == 1) {
            $query->where(['bill_status' => 1]);
        }

        $money = $query->sum('bill_money');

        return $money ? $money : '';
    }

    /**
     * 修改账单金额
     * @param array $params
     * @return mixed
     */
    public static function updateProductBillMoneyById($params = [])
    {
        $update = UserBill::where(['user_id' => $params['userId'], 'id' => $params['billId']])
            ->update([
                'bill_money' => $params['bill_money'],
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);

        return $update;
    }

    /**
     * 根据用户id查用户下的、未删除的、已统计完的平台负债信息
     * @bill_count_month 账单统计月份 1970-01
     * @param string $userId
     * @return array
     */
    public static function fetchMonthBillStatisticsByUserId($userId = '')
    {
        //当前月份
        $now = date('Y-m', time());

        $statistics = UserBillCount::select(['user_id', 'product_name', 'bank_conf_id', 'bank_sname', 'total_debts', 'debts', 'count_percent', 'bill_platform_type'])
            ->where(['user_id' => $userId])
            ->where('debts', '!=', 0)
            ->where(['bill_count_month' => $now])
            ->orderBy('count_percent', 'desc')
            ->get()
            ->toArray();

        return $statistics ? $statistics : [];
    }

    /**
     * 12个月的折线图
     * @bill_count_month 账单统计月份 1970-01
     * @param string $userId
     * @return array
     */
    public static function fetchYearBillStatisticsByUserId($userId = '')
    {
        //当前年月
        $now_year_month = date('Y-m', time());
        //前8个月
        $before_month = date('Y-m', strtotime("$now_year_month -8 month"));
        //后三个月
        $after_month = date('Y-m', strtotime("$now_year_month +3 month"));

        $statistics = UserBillCount::select(['user_id', 'product_name', 'bank_conf_id', 'bank_sname', 'total_debts', 'count_percent', 'bill_platform_type', 'bill_count_month'])
            ->addSelect([
                DB::raw("MAX('bill_count_month') as max_count_month"),
                DB::raw("MIN('bill_count_month') as min_count_month"),
            ])
            ->where(['user_id' => $userId])
            ->where('debts', '!=', 0)
            ->where('bill_count_month', '>=', $before_month)
            ->where('bill_count_month', '<=', $after_month)
            ->orderBy('bill_count_month', 'asc')
            ->groupBy('bill_count_month')
            ->get()
            ->toArray();

        return $statistics ? $statistics : [];
    }

    /**
     * 区间范围
     * @param string $userId
     * @return array
     */
    public static function fetchMonthRegionBillStatistics()
    {
        //当前年月
        $now_year_month = date('Y-m', time());
        //前8个月
        $before_month = date('Y-m', strtotime("$now_year_month -9 month"));
        //后三个月
        $after_month = date('Y-m', strtotime("$now_year_month +2 month"));

        //日期区间范围
        $regions = [];
        while ($before_month <= $after_month) {
            $before_month = date('Y-m', strtotime("$before_month +1 month"));
            $regions[] = $before_month;
        }

        return $regions ? $regions : [];
    }

    /**
     * 网贷账单详情借款时间 最小还款时间前一个月
     * @param array $billIds
     * @return string
     */
    public static function fetchMinProductRepayTimeByIds($billIds = [])
    {
        $query = UserBill::select(['id', 'bank_bill_time', 'repay_time', 'bill_money', 'bill_status', 'product_bill_period_num'])
            ->where(['is_delete' => 0])
            ->whereIn('id', $billIds)
            ->orderBy('repay_time', 'asc')
            ->limit(1)
            ->first();

        return $query ? $query->repay_time : '';
    }

    /**
     * 导入、未还、逾期账单
     * @param array $params
     * @return int
     */
    public static function fetchBillOverdueMoney($params = [])
    {
        //当前时间
        $now = date('Y-m-d', time());

        $overdueMoney = UserBill::select(['id', 'bank_bill_time', 'repay_time', 'bill_money', 'bill_status'])
            ->where(['bill_type' => 1, 'is_import' => 1, 'is_delete' => 0, 'is_hidden' => 0, 'bill_status' => 0])
            ->whereIn('id', $params['billIds'])
            ->orderBy('bank_bill_time', 'desc')
            ->where('repay_time', '<', $now)
            ->sum('bill_money');

        return $overdueMoney ? $overdueMoney : 0;
    }
}