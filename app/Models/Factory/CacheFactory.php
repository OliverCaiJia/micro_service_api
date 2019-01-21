<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Class CacheFactory
 * @package App\Models\Factory
 * Cache
 */
class CacheFactory extends AbsModelFactory
{

    // cache存储  7天
    public static function putValueToCache($key, $value)
    {
        return Cache::put($key, $value, Carbon::now()->second(7 * 24 * 3600));
    }

    //永久存储
    public static function putValueToCacheForever($key, $value)
    {
        return Cache::forever($key, $value, Carbon::now());
    }

    //存储7200秒
    public static function putValueToCacheTwoMinutes($key, $value)
    {
        return Cache::put($key, $value, Carbon::now()->second(7200));
    }

    // 存储1小时
    public static function putValueToCacheOneHour($key, $value)
    {
        return Cache::put($key, $value, Carbon::now()->hour(1));
    }

    // 从cache 读取数据
    public static function getValueFromCache($key)
    {
        return Cache::get($key);
    }

    // cache 中数据是否存在
    public static function existValueFromCache($key)
    {
        return Cache::get($key) ? true : false;
    }

    // 自增
    public static function incrementToCache($key,$value = 1)
    {
        return Cache::increment($key,$value);
    }
}
