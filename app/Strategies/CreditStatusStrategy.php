<?php

namespace App\Strategies;

use App\Strategies\AppStrategy;

/**
 * Class CreditStatusStrategy
 * @package App\Strategies
 * 积分状态策略
 */
class CreditStatusStrategy extends AppStrategy
{
    /**
     * @param array $params
     * @return bool
     * 小于最大次数 才可以继续增加次数
     */
    public static function fetchCreditStatusCount($params = [])
    {
        if ($params['count'] < $params['max_count']) {
            return true;
        } else {
            return false;
        }
    }
}