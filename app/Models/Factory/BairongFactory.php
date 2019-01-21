<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BairongFactory extends AbsModelFactory
{
    const BAIRONG_TOKENID = 'sd_bairong_tokenid';
    const BAIRONG_PHONE_ONE = 'sd_bairong_phone';
    const BAIRONG_PHONE_DATA = 'sd_bairong_phone_data';

    /**
     * 增量为 1
     *
     * @param $openid
     * @return bool|int
     */
    public static function incrementCache($key)
    {
        return Cache::increment($key);
    }
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
