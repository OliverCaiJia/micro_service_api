<?php

namespace App\Models\Factory;

use App\Constants\ConfigConstant;
use App\Models\AbsModelFactory;
use App\Models\Orm\SystemConfig;

/**
 * 系统配置处理工厂类
 */
class ConfigFactory extends AbsModelFactory
{

    /**
     * @return int
     * 返回额外奖金值
     */
    public static function getExtraData($param)
    {
        $extraArr = SystemConfig::select(['value'])
            ->where(['nid' => $param, 'status' => 1])
            ->first();
        return $extraArr ? $extraArr->value : ConfigConstant::DEFAULT_EMPTY;
    }

}
