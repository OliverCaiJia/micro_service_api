<?php

namespace App\MongoDB\Factory\Credit;

use App\MongoDB\Factory\MongoModelFactory;
use App\MongoDB\Orm\CarrierPhoneChannel;
use App\MongoDB\Orm\CarrierPhoneChannelLog;
use App\MongoDB\Orm\CreditCard;
use App\MongoDB\Orm\Mobile;
use App\Services\Core\Credit\CreditService;

class CarrierFactory extends MongoModelFactory
{
    /**
     * 添加运营商手机通道
     *
     * @param array $data
     * @return mixed
     */
    public static function createPhoneChannelInfo($data = [], $mark)
    {
        $info = CarrierPhoneChannel::where(['mark' => $mark])->first();
        if($info)
        {
            $res = CarrierPhoneChannel::where(['mark' => $mark])->update($data);
        } else {
            $res = CarrierPhoneChannel::insert($data);
        }

        return $res;
    }

    /**
     * 添加运营商手机通道日志
     *
     * @param array $data
     * @return mixed
     */
    public static function createPhoneChannelInfoLog($data = [])
    {
        return CarrierPhoneChannel::insert($data);
    }

}