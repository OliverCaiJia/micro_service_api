<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\PlatformProductProperty;
use App\Models\Orm\PlatformProductPropertyType;

/**
 * Class PushFactory
 * @package App\Models\Factory
 * 产品类型
 */
class ProductPropertyFactory extends AbsModelFactory
{
    /**
     * @param $type
     * @return string
     * 根据type值获取id
     */
    public static function fetchIdByKey($type)
    {
        $id = PlatformProductPropertyType::select(['id'])
            ->where(['type' => $type])
            ->first();

        return $id ? $id->id : '';
    }

    /**
     * @param $productId
     * @param string $times
     * @return int
     * 产品新类型值
     */
    public static function fetchPropertyValue($productId, $key = '')
    {
        $timesValue = PlatformProductProperty::where(['product_id' => $productId, 'key' => $key])
            ->select(['value'])->first();

        //默认值 1 控制倍率默认为1倍
        return $timesValue ? $timesValue->value : 1;
    }

    /**
     * @param $productId
     * @param string $key
     * @return int
     */
    public static function fetchProductPropertyValue($productId, $key = '')
    {
        $timesValue = PlatformProductProperty::where(['product_id' => $productId, 'key' => $key])
            ->select(['value'])->first();

        //默认值 1 控制倍率默认为1倍
        return $timesValue ? $timesValue->value : '';
    }
}