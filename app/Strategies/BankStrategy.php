<?php

namespace App\Strategies;

use App\Models\Factory\BankFactory;
use App\Models\Factory\CacheFactory;
use App\Strategies\AppStrategy;

/**
 * 银行公共策略
 *
 * @package App\Strategies
 */
class BankStrategy extends AppStrategy
{
    /**
     * @param $accountRes
     * @param $bankArr
     * @param $data
     * @return array
     * 基础信息 —— 验证银行名称
     */
    public static function getValidateBankName($accountRes, $bankArr, $data)
    {
        $name             = isset($data['name']) ? trim($data['name']) : '';
        $bankData['id']   = !empty($bankArr['id']) ? $bankArr['id'] : '';
        $bankData['name'] = !empty($bankArr['name']) ? trim($bankArr['name']) : '';

        if ($bankArr && $name == $accountRes['bankName']) {
            $bankData['bankSign'] = 1;
        } else {
            $bankData['bankSign'] = 0;
        }

        return $bankData ? $bankData : [];
    }

    /**
     * @param $accountRes
     * @param $bankArr
     * 基础信息 —— 获取银行名称【h5专用】
     */
    public static function getValidateBankNameH5($accountRes, $bankArr)
    {
        $bankData['id']   = !empty($bankArr['id']) ? $bankArr['id'] : '';
        $bankData['name'] = !empty($bankArr['name']) ? trim($bankArr['name']) : '';
        return $bankData ? $bankData : [];
    }

    /**
     * @param $bankCounts
     * @param $redisBankCounts
     * @return int
     * 基础信息 —— 银行列表数据是否更新
     */
    public static function getBankCounts($bankCounts)
    {
        $bankCountSign = 0;
        if (CacheFactory::existValueFromCache('bankCounts')) {
            $count = CacheFactory::getValueFromCache('bankCounts');
            if ($count == $bankCounts) {
                $bankCountSign = 0;
            } else {
                CacheFactory::putValueToCache('bankCounts', $bankCounts);
                $bankCountSign = 1;
            }
        } else {
            CacheFactory::putValueToCache('bankCounts', $bankCounts);
        }
        $bankCountsRes['bankcountsSign'] = $bankCountSign;

        return $bankCountsRes ? $bankCountsRes : [];
    }

    /**
     * @param $userAccount
     * @param $bankArr
     * @return array
     * 基础信息 —— 数据处理 得到 Account & Name
     */
    public static function getAccountAndName($userAccount, $bankArr)
    {
        $userBanks['name']    = !empty($bankArr['name']) ? $bankArr['name'] : '';
        $userBanks['account'] = isset($userAccount['account']) ? $userAccount['account'] : '';

        return $userBanks ? $userBanks : [];
    }
}
