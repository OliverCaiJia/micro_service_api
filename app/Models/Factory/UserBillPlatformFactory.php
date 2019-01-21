<?php

namespace App\Models\Factory;

use App\Constants\UserBillPlatformConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserBill;
use App\Models\Orm\UserBillBankConf;
use App\Models\Orm\UserBillBankType;
use App\Models\Orm\UserBillPlatform;
use App\Models\Orm\UserBillPlatformBillRel;
use App\Models\Orm\UserBillPlatformLog;
use App\Models\Orm\UserBillTask;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * 用户账单平台相关工厂类
 * Class UserBillFactory
 * @package App\Models\Factory
 */
class UserBillPlatformFactory extends AbsModelFactory
{
    /**
     * 根据唯一标识找到类型id
     * 账单银行配置类型
     * @status 状态, 1 使用, 0未使用
     * @param string $typeNid
     * @return int
     */
    public static function fetchBillBankTypeIdByNid($typeNid = '')
    {
        $typeId = UserBillBankType::where(['type_nid' => $typeNid, 'status' => 1])
            ->value('id');

        return $typeId ? $typeId : 0;
    }

    /**
     * 根据类型id获取可添加信用卡的银行列表
     * @status 状态, 1 使用, 0未使用
     * @param int $typeId
     * @return array
     */
    public static function fetchBanksByTypeId($typeId = 0)
    {
        $banks = UserBillBankConf::select(['id', 'bank_short_name', 'bank_logo', 'bank_scorpio_bname'])
            ->where(['type_id' => $typeId, 'status' => 1])
            ->orderBy('sort', 'asc')
            ->get()->toArray();

        return $banks ? $banks : [];
    }

    /**
     * 添加信用卡账单平台流水
     * @bank_is_import  导入类型 (0手动, 1魔蝎导入)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @bill_platform_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @is_hidden 是否隐藏(0未隐藏, 1已隐藏)
     * @param array $params
     * @return bool
     */
    public static function createCreditcardLog($params = [])
    {
        $query = new UserBillPlatformLog();
        $query->user_id = $params['userId'];
        $query->bank_conf_id = isset($params['billBankId']) ? $params['billBankId'] : 0;
        $query->bank_credit_card_num = isset($params['creditcardNum']) ? $params['creditcardNum'] : '';
        $query->bank_bill_date = isset($params['billDate']) ? $params['billDate'] : '';
        $query->bank_quota = isset($params['quota']) ? $params['quota'] : 0;
        $query->bank_is_import = 0;
        $query->bank_is_besides = 0;
        $query->bank_repay_day = isset($params['repayDate']) ? $params['repayDate'] : '';
        //网贷
        $query->product_id = isset($params['productId']) ? $params['productId'] : 0;
        $query->product_name = isset($params['productName']) ? $params['productName'] : '';
        $query->product_period_total = isset($params['productPeriodTotal']) ? $params['productPeriodTotal'] : 0;
        $query->product_repay_day = isset($params['productRepayDay']) ? $params['productRepayDay'] : '';
        //公共部分
        $query->repay_alert_status = $params['alertStatus'];
        $query->bill_platform_type = isset($params['bill_platform_type']) ? $params['bill_platform_type'] : 1;
        $query->is_delete = 0;
        $query->is_hidden = 0;
        $query->user_agent = UserAgent::i()->getUserAgent();
        $query->created_at = date('Y-m-d H:i:s', time());
        $query->created_ip = Utils::ipAddress();

        return $query->save();
    }

    /**
     * 创建或修改信用卡
     * @bank_is_import  导入类型 (0手动, 1魔蝎导入)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @bill_platform_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @is_hidden 是否隐藏(0未隐藏, 1已隐藏)
     * @param array $params
     * @return mixed
     */
    public static function createOrUpdateCreditcard($params = [])
    {
        $query = UserBillPlatform::where([
            'user_id' => $params['userId'],
            'id' => $params['creditcardId'],
            'is_delete' => 0,
            'bill_platform_type' => 1,
            'bank_is_import' => 0,
        ])->first();
        if (!$query) {
            $query = new UserBillPlatform();
            $query->created_at = date('Y-m-d H:i:s', time());
            $query->created_ip = Utils::ipAddress();
        }

        $query->user_id = $params['userId'];
        $query->bank_conf_id = $params['billBankId'];
        $query->bank_credit_card_num = $params['creditcardNum'];
        $query->bank_bill_date = $params['billDate'];
        $query->bank_quota = $params['quota'];
        $query->bank_is_import = 0;
        $query->bank_is_besides = 0;
        $query->bank_repay_day = $params['repayDate'];
        $query->repay_alert_status = $params['alertStatus'];
        $query->bill_platform_type = 1;
        $query->is_delete = 0;
        $query->is_hidden = 0;
        $query->user_agent = UserAgent::i()->getUserAgent();
        $query->updated_at = date('Y-m-d H:i:s', time());
        $query->updated_ip = Utils::ipAddress();
        $re = $query->save();

        $res['id'] = $re ? $query->id : 0;
        $res['bank_conf_id'] = $re ? $query->bank_conf_id : 0;
        return $res;
    }

    /**
     * 修改之前展示信用卡信息
     * @bank_is_import  导入类型 (0手动, 1魔蝎导入)
     * @bill_platform_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @param array $params
     * @return array
     */
    public static function fetchCreditcardInfoById($params = [])
    {
        $info = UserBillPlatform::select(['id', 'bank_conf_id', 'bank_credit_card_num', 'bank_bill_date', 'bank_quota', 'bank_repay_day', 'repay_alert_status', 'bank_is_import'])
            ->where([
                'user_id' => $params['userId'],
                'id' => $params['creditcardId'],
                'is_delete' => 0,
                'bill_platform_type' => 1,
                'bank_is_import' => 0,
            ])->first();

        return $info ? $info->toArray() : [];
    }

    /**
     *
     * @param array $params
     * @return array
     */
    public static function fetchBankInfoById($params = [])
    {
        $info = UserBillBankConf::select(['id', 'bank_short_name', 'bank_logo', 'bank_watermark_link', 'bank_bg_color'])
            ->where(['id' => $params['bank_conf_id'], 'type_id' => $params['typeId'], 'status' => 1])
            ->first();

        return $info ? $info->toArray() : [];
    }

    /**
     * 未删除、信用卡总张数
     * @bill_platform_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @param $userId
     * @return int
     */
    public static function fetchCreditcardCount($userId)
    {
        $count = UserBillPlatform::select(['id'])
            ->where(['user_id' => $userId, 'is_delete' => 0, 'bill_platform_type' => 1, 'bank_is_besides' => 0])
            ->count();

        return $count ? $count : 0;
    }

    /**
     * 在有效期内的账单任务
     * @param $userId
     * @return array
     */
    public static function fetchBillTask($userId)
    {
        $now = date('Y-m-d H:i:s', time());
        $res = UserBillTask::select(['id', 'step', 'is_complete', 'simple_bill_num', 'email_onlinebank'])
            ->where(['user_id' => $userId])
            ->orderBy('updated_at', 'desc')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->first();

        return $res ? $res->toArray() : [];
    }

    /**
     * 有效任务条数
     * @param $userId
     * @return int
     */
    public static function fetchBillTaskCount($userId)
    {
        $startTime = date('Y-m-d 00:00:00', time());
        $endTime = date('Y-m-d 23:59:59', time());
        $res = UserBillTask::select(['id', 'step', 'is_complete', 'simple_bill_num', 'email_onlinebank'])
            ->where(['user_id' => $userId])
            ->orderBy('updated_at', 'desc')
            ->where('start_time', '>=', $startTime)
            ->where('start_time', '<=', $endTime)
            ->where('step', '!=', 0)
            ->count();

        return $res ? $res : 0;
    }

    /**
     * 信用卡平台列表数据
     * @bill_platform_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @param string $userId
     * @return array
     */
    public static function fetchCreditcards($userId = '')
    {
        $query = UserBillPlatform::select(['id', 'bank_conf_id', 'bank_credit_card_num', 'bank_name_on_card', 'bank_bill_date', 'bank_quota', 'bank_use_points', 'bank_min_amount', 'bank_repay_amount', 'bank_repay_day', 'repay_alert_status', 'bank_is_import', 'is_hidden'])
            ->where(['user_id' => $userId])
            ->where(['is_delete' => 0, 'bill_platform_type' => 1, 'bank_is_besides' => 0])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        $creditcards = $query->get()->toArray();

        return $creditcards ? $creditcards : [];
    }

    /**
     * 根据平台id获取账单ids
     * @param string $billPlatformId
     * @return array
     */
    public static function fetchBillIdsById($billPlatformId = '')
    {
        $billIds = UserBillPlatformBillRel::select(['bill_id'])
            ->where(['bill_platform_id' => $billPlatformId])
            ->pluck('bill_id')
            ->toArray();

        return $billIds ? $billIds : [];
    }

    /**
     * 该账单日之前的负债总额
     * @param array $params
     * @return int
     */
    public static function fetchUserBillDebtTotalByIds($params = [])
    {
        $total = UserBill::select(['bill_money'])
            ->whereIn('id', $params['billIds'])
            ->where('bill_status', '!=', 1)
            ->where('bank_bill_time', '<', $params['billDate'])
            ->sum('bill_money');

        return $total ? $total : 0;
    }

    /**
     * 删除信用卡
     * @param array $params
     * @return mixed
     */
    public static function deleteBillPlatformById($params = [])
    {
        $delete = UserBillPlatform::where(['user_id' => $params['userId'], 'id' => $params['billPlatformId'], 'is_delete' => 0])
            ->update([
                'is_delete' => 1,
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);

        return $delete;
    }

    /**
     * 修改还款提醒状态
     * @param array $params
     * @return mixed
     */
    public static function updateStatus($params = [])
    {
        $update = UserBillPlatform::where(['user_id' => $params['userId'], 'id' => $params['billPlatformId'], 'is_delete' => 0])
            ->update([
                'repay_alert_status' => $params['alertStatus'],
                'is_hidden' => $params['hiddenStatus'],
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);

        return $update;
    }

    /**
     * 导入银行总数、新账单总数
     * @bill_platform_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @param $userId
     * @return array
     */
    public static function fetchImportBankIds($userId)
    {
        $query = UserBillPlatform::select(['id', 'bank_conf_id', 'bank_credit_card_num', 'bank_name_on_card', 'bank_new_bills_count', 'bank_is_besides'])
            ->where(['user_id' => $userId])
            ->where(['bank_is_import' => 1, 'bill_platform_type' => 1, 'is_delete' => 0, 'bank_is_besides' => 0, 'bank_is_newest' => 1]);

        $data['bankIds'] = $query->pluck('bank_conf_id')->toArray();
        $data['newBillCounts'] = $query->sum('bank_new_bills_count');

        return $data ? $data : [];
    }

    /**
     * 导入结果 信用卡平台列表
     * @bill_platform_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @bank_is_newest 是否最新(0旧版, 1最新)
     * @param array $params
     * @return array
     */
    public static function fetchCreditcardImportResultsByUserId($params = [])
    {
        $query = UserBillPlatform::select(['id', 'bank_conf_id', 'bank_credit_card_num', 'bank_name_on_card', 'bank_new_bills_count'])
            ->where(['user_id' => $params['userId'], 'bank_is_besides' => $params['bank_is_besides']])
            ->where(['bank_is_import' => 1, 'bill_platform_type' => 1, 'is_delete' => 0, 'bank_is_newest' => 1]);

        //排序 未超出15张在前、新账单数量大的在前
        $query->orderBy('bank_new_bills_count', 'desc')->orderBy('id', 'desc');

        $creditcards = $query->get()->toArray();

        return $creditcards ? $creditcards : [];
    }

    /**
     * 导入结果页  最新账单数据
     * @param array $creditcards
     * @return array
     */
    public static function fetchBillImportResults($creditcards = [])
    {
        foreach ($creditcards as $key => $value) {
            //银行信息
            //账单银行唯一标识
            $typeNid = UserBillPlatformConstant::BILL_PLATFORM_BANKS;
            //账单银行类型id
            $typeId = UserBillPlatformFactory::fetchBillBankTypeIdByNid($typeNid);
            //银行列表
            $bank['bank_conf_id'] = $value['bank_conf_id'];
            $bank['typeId'] = $typeId;
            $banks = UserBillPlatformFactory::fetchBankInfoById($bank);
            $creditcards[$key]['bank_short_name'] = isset($banks['bank_short_name']) ? $banks['bank_short_name'] : '';
            $creditcards[$key]['bank_logo'] = isset($banks['bank_logo']) ? QiniuService::getImgs($banks['bank_logo']) : '';
            //最新账单ids
            $value['newestBillIds'] = UserBillFactory::fetchRelBillIdsById($value['id']);
            //最新账单信息
            $creditcards[$key]['bills'] = UserBillFactory::fetchNewestBillsByLimit($value);
        }

        return $creditcards ? $creditcards : [];
    }

    /**
     * 该用户下所有平台ids
     * @is_delete 是否删除(0未删除, 1已删除)
     * @is_hidden 是否隐藏(0未隐藏, 1已隐藏)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @param array $params
     * @return array
     */
    public static function fetchBillPlatformTotalIdsByUserId($params = [])
    {
        $billType = isset($params['billType']) ? $params['billType'] : 0;
        $query = UserBillPlatform::select(['id'])
            ->where(['user_id' => $params['userId']])
            ->where(['is_delete' => 0, 'bank_is_besides' => 0]);

        $query->when($billType, function ($query) use ($billType) {
            $query->where(['bill_platform_type' => $billType]);
        });

        $platformIds = $query->pluck('id')
            ->toArray();

        return $platformIds ? $platformIds : [];
    }

    /**
     * 用户手动输入、导入平台ids
     * @is_delete 是否删除(0未删除, 1已删除)
     * @is_hidden 是否隐藏(0未隐藏, 1已隐藏)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @param array $params
     * @return array
     */
    public static function fetchBillPlatformIdsByUserId($params = [])
    {
        $billType = isset($params['billType']) ? $params['billType'] : 0;
        $query = UserBillPlatform::select('id')
            ->where(['bank_is_import' => $params['bank_is_import'], 'user_id' => $params['userId']])
            ->where(['is_delete' => 0, 'bank_is_besides' => 0, 'is_hidden' => 0]);

        $query->when($billType, function ($query) use ($billType) {
            $query->where(['bill_platform_type' => $billType]);
        });

        $ids = $query->pluck('id')->toArray();

        return $ids ? $ids : [];
    }


    /**
     * 未隐藏的账单
     * @is_delete 是否删除(0未删除, 1已删除)
     * @is_hidden 是否隐藏(0未隐藏, 1已隐藏)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @param array $params
     * @return array
     */
    public static function fetchNotHiddenBillPlatformIdsByUserId($params = [])
    {
        $billType = isset($data['billType']) ? $data['billType'] : 0;
        $query = UserBillPlatform::select('id')
            ->where(['bank_is_import' => $params['bank_is_import'], 'user_id' => $params['userId']])
            ->where(['is_delete' => 0, 'bank_is_besides' => 0]);

        $query->when($billType, function ($query) use ($billType) {
            $query->where(['bill_platform_type' => $billType]);
        });

        $ids = $query->pluck('id')->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 单个平台信息
     * @param string $platformId
     * @return array
     */
    public static function fetchPlatformInfoById($platformId = '')
    {
        $query = UserBillPlatform::select(['id', 'bank_conf_id', 'bank_credit_card_num', 'bank_name_on_card', 'product_id', 'product_name', 'product_period_total', 'product_repay_day', 'bank_bill_date', 'bank_repay_amount', 'bank_is_import', 'bill_platform_type', 'bank_repay_day', 'product_id', 'product_name', 'product_repay_day', 'created_at'])
            ->where(['id' => $platformId])
            ->where(['is_delete' => 0, 'bank_is_besides' => 0]);

        $platformInfo = $query->first();

        return $platformInfo ? $platformInfo->toArray() : [];
    }

    /**
     * 创建或修改网贷
     * @bank_is_import  导入类型 (0手动, 1魔蝎导入)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @bill_platform_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @is_hidden 是否隐藏(0未隐藏, 1已隐藏)
     * @param array $params
     * @return mixed
     */
    public static function createOrUpdateProduct($params = [])
    {
        $query = UserBillPlatform::where([
            'user_id' => $params['userId'],
            'id' => $params['billProductId'],
            'is_delete' => 0,
            'bill_platform_type' => $params['bill_platform_type'],
            'bank_is_import' => 0,
        ])->first();
        if (!$query) {
            $query = new UserBillPlatform();
            $query->created_at = date('Y-m-d H:i:s', time());
            $query->created_ip = Utils::ipAddress();
        }

        $query->user_id = $params['userId'];
        $query->product_id = $params['productId'];
        $query->product_name = $params['productName'];
        $query->product_period_total = $params['productPeriodTotal'];
        $query->product_repay_day = $params['productRepayDay'];
        $query->bill_platform_type = $params['bill_platform_type'];
        $query->repay_alert_status = $params['alertStatus'];
        $query->is_delete = 0;
        $query->is_hidden = 0;
        $query->user_agent = UserAgent::i()->getUserAgent();
        $query->updated_at = date('Y-m-d H:i:s', time());
        $query->updated_ip = Utils::ipAddress();
        $re = $query->save();

        $res['id'] = $re ? $query->id : 0;
        $res['product_period_num'] = $re ? $query->product_period_num : 0;
        return $res;
    }

    /**
     * @bill_platform_type '网贷或信用卡类型 (1信用卡, 2网贷)',
     * @is_delete '是否删除(0未删除, 1已删除)',
     * @is_hidden '是否隐藏(0未隐藏, 1已隐藏)',
     * @param array $params
     * @return array
     */
    public static function fetchProduct($params = [])
    {
        $query = UserBillPlatform::select(['id', 'user_id', 'product_id', 'product_name', 'product_period_total', 'product_repay_day', 'repay_alert_status'])
            ->where(['bill_platform_type' => 2, 'is_delete' => 0, 'is_hidden' => 0])
            ->where(['user_id' => $params['userId'], 'id' => $params['billProductId']]);

        $product = $query->first();

        return $product ? $product->toArray() : [];
    }

    /**
     * 信用卡对应最近一期账单信息
     * @param array $creditcards
     * @return array
     */
    public static function fetchCreditcardPlatformAndBillInfo($creditcards = [])
    {
        $billInfo = [];
        foreach ($creditcards as $key => $item) {
            //手动输入：需要未还账单信息，没有未还显示--
            //导入：需要未还、已还账单信息，状态与首页列表一致
            if ($item['bank_is_import'] == 0) {
                //查询未还账单信息
                //账单id
                $billIds = UserBillFactory::fetchRelBillIdsById($item['id']);
                //未还账单信息
                $billInfo = UserBillFactory::fetchWaitBillInfoByBillId($billIds);

            } elseif ($item['bank_is_import'] == 1) {
                //需要未还、已还账单信息
                //账单id
                $billIds = UserBillFactory::fetchRelBillIdsById($item['id']);
                //未还账单信息
                $billInfo = UserBillFactory::fetchImportBillInfoByBillId($billIds);
            }

            //该平台下所有账单id
            $billIds = UserBillFactory::fetchRelBillIdsById($item['id']);
            //找到最近的一个账单
            $bill = UserBillFactory::fetchNearestBill($billIds);
            if ($bill) {
                //当前账单日
                $bank_bill_time = $bill['bank_bill_time'];
                $strto_bank_bill_time = strtotime($bank_bill_time);
                //下一个账单日
                $next_bill_time = date('Y-m-d', strtotime("$bank_bill_time +1 month"));
                $data['strto_bank_bill_time'] = $strto_bank_bill_time;
                $data['strto_next_bill_time'] = strtotime($next_bill_time);
                $creditcards[$key]['billCount'] = UserBillFactory::fetchIsWithin($data);
            } else {
                $creditcards[$key]['billCount'] = 0;
            }

            //账单银行唯一标识
            $typeNid = UserBillPlatformConstant::BILL_PLATFORM_BANKS;
            //账单银行类型id
            $item['typeId'] = UserBillPlatformFactory::fetchBillBankTypeIdByNid($typeNid);
            //银行信息
            $creditcards[$key]['bankInfo'] = UserBillPlatformFactory::fetchBankInfoById($item);
            $creditcards[$key]['bill_money'] = $billInfo ? $billInfo['bill_money'] : 0;
            $creditcards[$key]['bill_status'] = $billInfo ? $billInfo['bill_status'] : 0;
            $creditcards[$key]['repay_time'] = $billInfo ? $billInfo['repay_time'] : '';
            $creditcards[$key]['bank_bill_time'] = $billInfo ? $billInfo['bank_bill_time'] : '';
            $creditcards[$key]['billinfo_sign'] = $billInfo ? 1 : 0;
            //账单ids
            $creditcards[$key]['billIds'] = $billIds;
        }

        return $creditcards ? $creditcards : [];
    }

    /**
     * 账单管理列表 【信用卡，网贷】
     * @param array $params
     * @return array
     */
    public static function fetchManages($params = [])
    {
        $pageSize = intval($params['pageSize']);
        $pageNum = intval($params['pageNum']);

        $query = UserBillPlatform::select(['id', 'product_id', 'product_name', 'created_at', 'bank_conf_id', 'bank_credit_card_num', 'bank_name_on_card', 'bank_bill_date', 'bank_repay_day', 'is_hidden', 'bill_platform_type', 'bank_is_import', 'bank_quota', 'bank_use_points', 'bank_min_amount', 'repay_alert_status'])
            ->where(['user_id' => $params['userId']])
            ->where(['bill_platform_type' => $params['bill_platform_type'], 'is_delete' => 0])
            ->where(['bank_is_besides' => 0]);

        //按创建时间倒序排序
        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        //分页
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
     * 获取每个网贷产品借款总额度
     * @param array $products
     * @return array
     */
    public static function fetchProductTotalMoney($products = [])
    {
        foreach ($products as $key => $item) {
            //账单信息
            $data['billIds'] = UserBillFactory::fetchRelBillIdsById($item['id']);
            //最低还款日期
            $min_repay_time = UserBillFactory::fetchMinProductRepayTimeByIds($data['billIds']);
            $loan_time = date('Y-m-d', strtotime("$min_repay_time -1 month"));

            //全部贷款金额
            $wait['billIds'] = $data['billIds'];
            $wait['money_sign'] = 0;
            $total_money = UserBillFactory::fetchProductBillMoneyById($wait);
            $products[$key]['total_money'] = empty($total_money) ? '0.00' : $total_money;
            //$products[$key]['created_at'] = isset($loan_time) ? $loan_time : '';
        }
        return $products;
    }

    /**
     * 最近一次、导入所有银行卡平台ids
     * @bill_platform_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @param $userId
     * @return array
     */
    public static function fetchNearImportBillPlatformIds($userId = '')
    {
        $query = UserBillPlatform::select(['id', 'bank_conf_id', 'bank_credit_card_num', 'bank_name_on_card', 'bank_new_bills_count', 'bank_is_besides'])
            ->where(['user_id' => $userId])
            ->where(['bank_is_import' => 1, 'bill_platform_type' => 1, 'is_delete' => 0, 'bank_is_besides' => 0]);

        $data = $query->pluck('id')->toArray();

        return $data ? $data : [];
    }

    /**
     * 最近一次、导入所有银行卡平台ids
     * @bill_platform_type 网贷或信用卡类型 (1信用卡, 2网贷)
     * @is_delete 是否删除(0未删除, 1已删除)
     * @bank_is_besides 导入信用卡是否超出数量(0未超出, 1已超出)
     * @param string $userId
     * @return string
     */
    public static function fetchNearImportBillPlatformTime($userId = '')
    {
        $query = UserBillPlatform::select(['id', 'bank_conf_id', 'bank_credit_card_num', 'bank_name_on_card', 'bank_new_bills_count', 'bank_is_besides', 'updated_at'])
            ->where(['user_id' => $userId])
            ->where(['bank_is_import' => 1, 'bill_platform_type' => 1, 'is_delete' => 0, 'bank_is_besides' => 0])
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(1)
            ->first();

        return $query ? $query->updated_at : 'YYYY-MM';
    }
}