<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\UserAlipay;

/**
 * Class AlipayFactory
 * @package App\Models\Factory
 * 支付宝相关工厂
 */
class AlipayFactory extends AbsModelFactory
{
    /**
     * @param $userId
     * 支付宝账号
     */
    public static function getAlipay($userId)
    {
        $alipay = UserAlipay::where(['user_id'=>$userId,'status'=>0])   //status为０表示可以使用
            ->select(['alipay'])->first();
        return $alipay ? $alipay->alipay : '';
    }
}