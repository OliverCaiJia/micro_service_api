<?php

namespace App\Models\Factory;

use App\Helpers\RestUtils;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\Bank;
use App\Models\Orm\BankCreditcardBill;
use App\Models\Orm\BankCreditcardBillLog;
use App\Models\Orm\CreditcardAccount;
use App\Models\Orm\CreditcardAccountLog;

/**
 * Class CreditcardAccount
 * @package App\Models\Factory
 * 信用卡账户工厂
 */
class CreditcardAccountFactory extends AbsModelFactory
{
    /**
     * @param $data
     * @return array
     * @status  是否有效(0,有效  1,无效)
     * 修改账户之前获取账户数据
     */
    public static function fetchBeforeAccount($data)
    {
        $account = CreditcardAccount::select(['id', 'bank_usage_id', 'credit_card_num', 'repay_day', 'repay_alert_status', 'bill_date', 'repay_amount'])
            ->where(['user_id' => $data['userId'], 'id' => $data['accountId']])
            ->where(['status' => 0])
            ->first();

        return $account ? $account->toArray() : [];
    }

    /**
     * @param $data
     * @return int
     * @status 是否有效(0,有效  1,无效)
     * 用户信用卡总张数
     */
    public static function fetchAccountCount($data)
    {
        $count = CreditcardAccount::where(['user_id' => $data['userId']])
            ->where(['status' => 0])
            ->count();

        return $count ? $count : 0;
    }

    /**
     * @param $data
     * @return bool
     * 信用卡账户流水
     */
    public static function createAccountLog($data)
    {
        $log = new CreditcardAccountLog();
        $log->user_id = $data['userId'];
        $log->bank_usage_id = $data['bankUsageId'];
        $log->registration_id = $data['registration_id'];
        $log->credit_card_num = $data['creditcardNum'];
        $log->repay_day = $data['repayDay'];
        $log->repay_alert_status = $data['repayAlertStatus'];
        $log->bill_date = $data['billDate'];
        $log->repay_amount = $data['repayAmount'];
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();
        return $log->save();
    }

    /**
     * @param $data
     * @return bool
     * 创建或修改信用卡账户
     */
    public static function createOrUpdateAccount($data)
    {
        $account = CreditcardAccount::select()
            ->where(['id' => $data['accountId'], 'user_id' => $data['userId']])
            ->where(['status' => 0])->first();
        if (empty($account)) {
            $account = new CreditcardAccount();
            $account->created_at = date('Y-m-d H:i:s', time());
            $account->created_ip = Utils::ipAddress();
        }
        $account->user_id = $data['userId'];
        $account->bank_usage_id = $data['bankUsageId'];
        $account->registration_id = $data['registration_id'];
        $account->credit_card_num = $data['creditcardNum'];
        $account->repay_day = $data['repayDay'];
        $account->repay_alert_status = $data['repayAlertStatus'];
        $account->bill_date = $data['billDate'];
        $account->repay_amount = $data['repayAmount'];
        $account->user_agent = UserAgent::i()->getUserAgent();
        $account->updated_at = date('Y-m-d H:i:s', time());
        $account->updated_ip = Utils::ipAddress();
        return $account->save();
    }

    /**
     * @param $data
     * @return bool
     * @status 账户状态 0存在 1不存在
     * 修改提醒状态
     */
    public static function updateRepayAlertStatus($data)
    {
        $status = CreditcardAccount::where(['user_id' => $data['userId'], 'id' => $data['accountId'], 'status' => 0])
            ->update(['repay_alert_status' => $data['repayAlertStatus'], 'updated_at' => date('Y-m-d H:i:s', time()), 'updated_ip' => Utils::ipAddress()]);

        return $status ? true : false;
    }

    /**
     * @param $params
     * @return bool
     *
     */
    public static function createBankCreditcardBillLog($params)
    {
        $log = new BankCreditcardBillLog();
        $log->account_id = $params['accountId'];
        $log->user_id = $params['userId'];
        $log->bill_money = $params['billMoney'];
        $log->bill_time = $params['billTime'];
        $log->bill_status = $params['billStatus'];
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();
        return $log->save();
    }

    /**
     * @param $param
     * @return string
     * @status 是否有效(0,有效  1,无效)
     */
    public static function fetchRepayday($param)
    {
        $repayday = CreditcardAccount::select(['repay_day'])
            ->where(['id' => $param, 'status' => 0])
            ->first();

        return $repayday ? $repayday->repay_day : '';
    }

    /**
     * @param $params
     * @return bool
     * @status 是否有效(0,有效  1,无效)
     * 创建或修改 信用卡提醒表
     */
    public static function createOrUpdateBill($params)
    {
        $bill = BankCreditcardBill::select(['id'])->where(['id' => $params['billId'], 'status' => 0])->first();
        if (!$bill && $params['billStatus'] == 0) {
            $bill = new BankCreditcardBill();
            $bill->created_at = date('Y-m-d H:i:s', time());
            $bill->created_ip = Utils::ipAddress();
            $bill->account_id = $params['accountId'];
        }
        $bill->bill_time = $params['billTime'];
        $bill->user_id = $params['userId'];
        $bill->bill_money = $params['billMoney'];
        $bill->bill_status = $params['billStatus'];
        $bill->user_agent = UserAgent::i()->getUserAgent();
        $bill->updated_at = date('Y-m-d H:i:s', time());
        $bill->updated_ip = Utils::ipAddress();
        return $bill->save();
    }

    /**
     * @param $params
     * @return array
     * @status 是否有效(0,有效  1,无效)
     * 验证是否存在该信用卡账户
     */
    public static function checkIsAccount($params)
    {
        $isAccount = CreditcardAccount::select(['id'])
            ->where(['id' => $params['accountId'], 'status' => 0])
            ->first();

        return $isAccount ? $isAccount->toArray() : [];
    }

    /**
     * @param $params
     * @return array
     * @status 是否有效(0,有效  1,无效)
     * 验证账单id是否存在
     */
    public static function checkIsBill($params)
    {
        $isBill = BankCreditcardBill::select(['id'])
            ->where(['id' => $params['billId'], 'status' => 0])
            ->first();

        return $isBill ? $isBill->toArray() : [];
    }

    /**
     * @param $params
     * @return array
     * 获取创建的账户id
     */
    public static function fetchAccountId($params)
    {
        $isAccount = CreditcardAccount::select(['id'])
            ->where(['user_id' => $params['userId'], 'bank_usage_id' => $params['bankUsageId'], 'credit_card_num' => $params['creditcardNum'], 'status' => 0])
            ->first();

        return $isAccount ? $isAccount->toArray() : [];
    }

    /**
     * @param $params
     * @return bool
     * @bill_status 还款账单状态(0,未还  1,已还)
     * 已还点击之后不可以进行修改
     */
    public static function fetchBillIsToUpdate($params)
    {
        $isBill = BankCreditcardBillLog::select(['id'])->where(['user_id' => $params['userId'], 'account_id' => $params['accountId'], 'bill_status' => 1, 'bill_time' => $params['billTime']])->first();

        return $isBill ? true : false;
    }

    /**
     * @param $params
     * @return bool
     * @bill_status 还款账单状态(0,未还  1,已还)
     * 修改账单状态为已还
     */
    public static function updateBillStatus($params)
    {
        $bill = BankCreditcardBill::select(['id'])->where(['user_id' => $params['userId'], 'id' => $params['billId'], 'status' => 0])->update(['bill_status' => 1, 'updated_at' => date('Y-m-d H:i:s', time()), 'updated_ip' => Utils::ipAddress()]);

        return $bill ? true : false;
    }

    /**
     * @param $data
     * @return array
     * @status 是否有效(0,有效  1,无效)
     * @online_status 上下线状态,0 下线, 1 上线
     * 用户账户信用卡
     */
    public static function fetchAccountsByUserId($data)
    {
        $userId = $data['userId'];
        $query = CreditcardAccount::from('sd_bank_creditcard_account as a')
            ->join('sd_bank_usage as b', 'b.id', '=', 'a.bank_usage_id');
        //信用卡账户数据
        $query->select(['a.id', 'a.bank_usage_id', 'a.credit_card_num', 'a.repay_day', 'a.repay_alert_status', 'bill_date', 'repay_amount']);
        //银行数据
        $query->addSelect(['b.bank_short_name', 'b.bank_logo']);
        //筛选条件
        $query->where(['a.user_id' => $userId])->where(['a.status' => 0, 'b.status' => 1]);
        //排序
        $query->orderBy('a.created_at', 'desc');
        $accounts = $query->get()->toArray();

        return $accounts ? $accounts : [];

    }

    /**
     * @param $params
     * @param $data
     * @return array
     * @bill_status 还款账单状态(0,未还  1,已还)
     * 未还账单
     */
    public static function fetchAccountbills($params, $data)
    {
        foreach ($params as $key => $val) {
            $bills = BankCreditcardBill::select(['id', 'bill_time', 'bill_money', 'bill_status'])
                ->where(['bill_status' => 0, 'account_id' => $val['id'], 'user_id' => $data['userId']])
                ->orderBy('updated_at', 'desc')->limit(1)->first();
            $params[$key]['bills'] = !empty($bills) ? $bills->toArray() : [];
        }

        return $params ? $params : [];
    }

    /**
     * @param $params
     * @return array
     * 获取账单数据  账单日期
     */
    public static function fetchBillTime($params)
    {
        $bill = BankCreditcardBill::select(['id', 'bill_time'])->where(['user_id' => $params['userId'], 'account_id' => $params['accountId'], 'id' => $params['billId'], 'status' => 0])->first();

        return $bill ? $bill->toArray() : [];
    }

    /**
     * @param $params
     * @return array
     * @bill_status 还款账单状态(0,未还  1,已还)
     * 已还账单
     * 默认显示3个
     */
    public static function fetchAccountbilleds($params, $data)
    {
        foreach ($params as $key => $val) {
            $billed = BankCreditcardBill::select(['id', 'bill_time', 'bill_money', 'bill_status'])
                ->where(['bill_status' => 1, 'account_id' => $val['id'], 'user_id' => $data['userId']])
                ->orderBy('updated_at', 'desc')
                ->limit(3)->get()->toArray();
            $params[$key]['billeds'] = !empty($billed) ? $billed : [];
        }

        return $params ? $params : [];
    }

    /**
     * @param $params
     * @return array
     * 获取账单id
     */
    public static function fetchBillId($params)
    {
        $isBill = BankCreditcardBill::select(['id'])->where(['user_id' => $params['userId'], 'account_id' => $params['accountId'], 'bill_time' => $params['billTime']])->first();

        return $isBill ? $isBill->toArray() : [];
    }

    /**
     * @param $params
     * @return array
     * 已还账单分页显示列表
     */
    public static function fetchBills($params)
    {
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;

        $query = BankCreditcardBill::select(['id', 'bill_time', 'bill_money', 'bill_status'])
            ->where(['bill_status' => 1, 'account_id' => $params['accountId'], 'user_id' => $params['userId']])
            ->orderBy('updated_at', 'desc');

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

        $datas['list'] = $bills;
        $datas['pageCount'] = $countPage ? $countPage : 0;

        return $datas ? $datas : [];

    }

}