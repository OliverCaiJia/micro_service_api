<?php

namespace App\Strategies;

use App\Helpers\Formater\NumberFormater;
use App\Models\Factory\UserFactory;
use App\Strategies\AppStrategy;

/**
 * 提现流水策略
 *
 * Class UserStrategy
 * @package App\Strategies
 */
class CashStrategy extends AppStrategy
{

    /**
     * 交易号
     */
    public static function creditNid()
    {
        $nid = date('Y') . date('m') . date('d') . date('H') . date('i') . date('s') . UserStrategy::getRandChar(6);
        return 'account-cash' . $nid;
    }

    /**
     * @param $accountCash
     * 提现流水 获取手机号&&金额
     */
    public static function getMobileAndTotal($accountCash)
    {
        foreach ($accountCash as $key => $val) {
            $mobile                      = UserFactory::fetchMobile($val['user_id']);
            $accountCash[$key]['mobile'] = NumberFormater::processPhoneNum($mobile);
            $accountCash[$key]['total']  = intval($val['total']);
        }
        return $accountCash;
    }

}
