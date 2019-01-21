<?php

namespace App\Models\Factory;

use App\Constants\UserVipConstant;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\AccountPayment;
use App\Models\Orm\UserOrder;
use App\Models\Orm\UserOrderType;


class UserOrderFactory extends AbsModelFactory
{
    /**
     * 创建订单
     *
     * @param $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function createOrder($data)
    {
        return UserOrder::updateOrCreate($data);
    }

    /**
     * 获取支付渠道ID
     *
     * @return mixed|string
     */
    public static function getPaymentType()
    {
        $id = AccountPayment::where(['nid' => UserVipConstant::PAYMENT_TYPE])->value('id');

        return $id ? $id : 1;
    }

    /**
     * 获取订单类型ID
     *
     * @return mixed|string
     */
    public static function getOrderType()
    {
        $id = UserOrderType::where(['type_nid' => UserVipConstant::ORDER_TYPE])->value('id');

        return $id ? $id : 1;
    }




}
