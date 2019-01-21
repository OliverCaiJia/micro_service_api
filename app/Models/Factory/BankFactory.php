<?php

namespace App\Models\Factory;

use App\Constants\BankConstant;
use App\Models\AbsModelFactory;
use App\Models\Orm\Banks;
use App\Models\Orm\UserAlipay;
use App\Models\Orm\UserBanks;

class BankFactory extends AbsModelFactory
{
    /**
     * @param $param
     * @return mixed
     * 获取用户支付宝信息
     */
    public static function fetchAlipayArray($user_id)
    {
        $alipayArr = UserAlipay::select(['alipay'])
            ->where(['user_id' => $user_id])
            ->first();
        return $alipayArr ? $alipayArr->toArray() : [];
    }

    /**
     * @param $param
     * @return mixed
     * 获取用户支付宝信息
     */
    public static function fetchAlipay($user_id)
    {
        $alipayArr = UserAlipay::select(['alipay'])
            ->where(['user_id' => $user_id, 'status' => 0])
            ->first();
        return empty($alipayArr) ? '' : $alipayArr->alipay;
    }

    /**
     * @card_use 使用状态【0信用资料，1认证银行】
     * @param $user_id
     * @return mixed
     * 返回用户账户信息
     */
    public static function fetchAccountsArray($user_id)
    {
        $userBank = UserBanks::select(['bank_id', 'account'])
            ->where(['user_id' => $user_id, 'status' => 0, 'card_use' => 0])
            ->first();
        $bankArr = [];
        if (!empty($userBank)) {
            $bankArr['name'] = $userBank->bank()->first()->name;
        }
        $bankArr['account'] = !empty($userBank['account']) ? $userBank['account'] : '';
        return $bankArr;
    }

    /**
     * @card_use 使用状态【0信用资料，1认证银行】
     * @param $user_id
     * @return mixed
     * 返回用户账户信息
     */
    public static function fetchAccounts($user_id)
    {
        $userBank = UserBanks::select(['account'])
            ->where(['user_id' => $user_id, 'status' => 0, 'card_use' => 0])
            ->first();
        return $userBank ? $userBank->account : '';
    }

    /**
     * 返回用户银行卡信息
     * @card_use 使用状态【0信用资料，1认证银行】
     * @param $param
     * @return array
     */
    public static function fetchBanksArray($param)
    {
        $userBanksArr = UserBanks::select(['bank_id', 'account'])
            ->where(['user_id' => $param, 'status' => 0, 'card_use' => 0])
            ->first();

        return $userBanksArr ? $userBanksArr->toArray() : [];

    }

    /**
     * 基础信息 —— 获取银行列表
     */
    public static function fetchBankLists()
    {
        $banks = Banks::select(['id', 'name'])
            ->where('status', '<>', 9)
            ->get()->toArray();

        return $banks ? $banks : [];
    }

    /**
     * @param $bankName
     * @return array
     * 通过银行名称查数据
     */
    public static function fetchAccountByName($bankName)
    {
        $bankObj = Banks::select(['id', 'name'])
            ->where(['name' => $bankName])
            ->first();

        return $bankObj ? $bankObj->toArray() : [];
    }

    //获取银行列表总数
    public static function fetchBankCounts()
    {
        $bankCounts = Banks::select()
            ->where('status', '<>', 9)
            ->count();

        return $bankCounts ? $bankCounts : 0;
    }

    // 根据银行id获取银行名称
    public static function fetchBankNameByBankId($userAccount)
    {
        if (empty($userAccount)) {
            return false;
        } elseif (empty($userAccount['account'])) {
            $userAccount['bank_id'] = 0;
        }

        $bankObj = Banks::select(['name', 'id'])
            ->where(['id' => $userAccount['bank_id']])
            ->first();
        return $bankObj ? $bankObj->toArray() : [];
    }

    // 根据银行id获取银行名称
    public static function fetchBanksByName($bankName)
    {
        $bankArr = Banks::select(['id', 'name'])
            ->where(['name' => $bankName])
            ->first();

        return $bankArr ? $bankArr->toArray() : [];
    }

    /**
     * 根据银行id获取单个银行信息
     * @param $id
     * @return array
     *
     */
    public static function fetchBankinfoById($id)
    {
        $bank = Banks::select(['id', 'no', 'nid', 'name', 'litpic', 'sname'])
            ->where(['id' => $id, 'status' => 0])
            ->first();

        return $bank ? $bank->toArray() : [];
    }

}
