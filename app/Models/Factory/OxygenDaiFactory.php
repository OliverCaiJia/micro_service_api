<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class OxygenDaiFactory extends AbsModelFactory
{
    const TOKENID = 'oxygen_access_token';
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
