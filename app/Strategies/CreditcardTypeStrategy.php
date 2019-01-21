<?php

namespace App\Strategies;

use App\Strategies\AppStrategy;

/**
 * Class CreditcardTypeStrategy
 * @package App\Strategies
 * 信用卡类型策略层
 */
class CreditcardTypeStrategy extends AppStrategy
{

    /**
     * @param $params
     * @param $datas
     * @return mixed
     * 信用卡用途类型
     */
    public static function getUsageType($params, $datas)
    {
        foreach ($datas as $key => $val) {
            foreach ($params as $k => $v) {
                $params[$key + 1]['id'] = $val['id'];
                $params[$key + 1]['name'] = $val['name'];
                $params[$key + 1]['type_nid'] = $val['type_nid'];
            }
        }

        return $params;
    }
}