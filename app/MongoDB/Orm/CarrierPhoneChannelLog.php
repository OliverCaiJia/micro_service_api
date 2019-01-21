<?php

namespace App\MongoDB\Orm;

use App\MongoDB\AbsMongoModel;

class CarrierPhoneChannelLog extends AbsMongoModel
{
    /**
     *
     *  设置集合名称
     */
    const COLLECTION_NAME = 'sd_mongo_carrier_phone_channel_log';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $collection  = self::COLLECTION_NAME;


}
