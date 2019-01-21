<?php

namespace App\Models\Cache;

use App\Models\AbsCacheFactory;
use Illuminate\Support\Facades\Cache;

class CommonCache extends AbsCacheFactory
{
    //你我贷存储token的redis的key
    const NIWODAI_TOKEN = 'oneloan_niwodai_token';

    /**
     * 获取数据
     *
     * @param $key
     * @return string
     */
    public static function getCache($key)
    {
        return Cache::get($key)?:'';
    }

    /**
     * 设置数据
     *
     * @param $key
     * @param $value
     * @param null $outTime
     */
    public static function setCache($key, $value, $outTime = null)
    {
        return Cache::put($key,$value,$outTime);
    }

    /**
     * 删除key
     *
     * @param $key
     * @return bool
     */
    public static function delCache($key)
    {
        return Cache::forget($key);
    }
}
