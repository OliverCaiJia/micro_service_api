<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use Cache;

/**
 * Created by PhpStorm.
 * User: root
 * Date: 16-12-14
 * Time: 上午10:50
 */
class PhoneFactory extends AbsModelFactory
{
    /**
     * @param $codeKey
     * @param $signKey
     * @param $code
     * @param $sign
     * @return bool
     * 验证短信验证码是否正确
     */
    public static function checkMobileAndCode($codeKey,$signKey,$code,$sign)
    {
        $signValueArr = Cache::get($signKey);
        //dd($signValueArr);
        #检查code以及sign
        if(Cache::has($codeKey) && Cache::has($signKey))
        {
            if(Cache::get($codeKey) == $code && $signValueArr['sign'] == $sign)
            {
                return true;
            }
        }
        return false;
    }

}
