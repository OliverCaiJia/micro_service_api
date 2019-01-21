<?php

namespace App\Models\Factory;

use App\Constants\UserVipConstant;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\AccountPayment;
use App\Models\Orm\AccountPaymentConfig;
use App\Models\Orm\SystemConfig;
use App\Models\Orm\UserOrder;
use App\Models\Orm\UserVip;
use App\Models\Orm\UserVipType;
use App\Strategies\UserVipStrategy;
use Illuminate\Support\Facades\DB;

/**
 * Class PaymentFactory
 * @package App\Models\Factory
 * 支付工厂
 */
class PaymentFactory extends AbsModelFactory
{
    /**
     * 获取订单状态
     *
     * @param $orderId
     * @return mixed
     */
    public static function getOrderStatus($orderId)
    {
        return UserOrder::where(['orderid' => $orderId])->value('status');
    }

    /**
     * 支付渠道ID
     *
     * @param string $shadowNid
     * @return mixed
     */
    public static function getPaymentConfig($shadowNid)
    {
        $config = AccountPaymentConfig::where(['shadow_nid' => $shadowNid, 'status' => 1])->orderBy('id','desc')->first();

        return $config ? $config->pay_id : 1;
    }

    /**
     * 获取渠道nid
     *
     * @param $paymentId
     * @return mixed
     */
    public static function getPaymentNid($paymentId)
    {
        return AccountPayment::where('id', $paymentId)->value('nid');
    }

    /**
     * 易宝公钥
     *
     * @return mixed
     */
    public static function getYibaoPublicKey($nid)
    {
        return AccountPayment::where('nid', $nid)->value('channel_public_key');
    }

    /**
     * 易宝商户私钥
     *
     * @return mixed
     */
    public static function getYibaoMerchantPrivateKey($nid)
    {
        return AccountPayment::where('nid', $nid)->value('merchant_private_key');
    }

    /**
     * 易宝商户公钥
     *
     * @return mixed
     */
    public static function getYibaoMerchantPublicKey($nid)
    {
        return AccountPayment::where('nid', $nid)->value('merchant_public_key');
    }

    /**
     * 获取易宝商户编号
     *
     * @return mixed
     */
    public static function getYibaoMerchantCode($nid)
    {
        return AccountPayment::where('nid', $nid)->value('merchant_code');
    }

    /**
     * 获取vip期限
     *
     * @return mixed
     */
    public static function getVipTime()
    {
        $value = UserVipType::where('type_nid', UserVipConstant::VIP_TYPE_NID)->value('vip_period');
        return isset($value) ? $value : 0;
    }


    /**
     * 更新VIP表
     *
     * @param $uid
     * @param $status
     * @param $vipType
     * @return bool
     */
    public static function updateUserVIPStatus($uid, $status, $vipType)
    {
        $typeId = UserVipFactory::getReVipTypeId($vipType);
        $price = UserVipFactory::getReVipAmount($vipType);
        $message = UserVip::select()->where(['user_id' => $uid, 'vip_type' => $typeId])->first();
        //根据不同类型处理不同的数据
        $data = UserVipStrategy::getDiffVipTypeDeal($message, $vipType);
        $time = isset($data['time']) ? $data['time'] : date('Y-m-d H:i:s', UserVipStrategy::getVipExpired());
        $res = UserVip::where(['user_id' => $uid])->update([
            'vip_type' => $typeId,
            'status' => $status,
            'open_time' => date('Y-m-d H:i:s', time()),
            'start_time' => date('Y-m-d H:i:s', time()),
            'end_time' => $time,//date('Y-m-d H:i:s', UserVipStrategy::getVipExpired()),
            'updated_at' => date('Y-m-d H:i:s', time()),
            'updated_ip' => Utils::ipAddress(),
            'total_consuming' => DB::raw("total_consuming + ".$price),//isset($message['total_consuming']) ? ($message['total_consuming'] + $price) : $price,
            'total_count' => DB::raw("total_count + 1"),//isset($message['total_count']) ? ($message['total_count'] + 1) : 1,
        ]);

        return ($res > 0) ? true : false;
    }

    /**
     * 更新订单表
     *
     * @param array $data
     * @return bool
     */
    public static function updateUserOrderStatus($data = [])
    {
        $responseTxt = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = UserOrder::where(['orderid' => $data['orderid']])->update([
            'payment_order_id' => $data['yborderid'],
            'orderid' => $data['orderid'],
            'lastno' => $data['lastno'],
            'cardtype' => $data['cardtype'],
            'amount' => number_format($data['amount'] / 100, 2),
            'status' => $data['status'],
            'response_text' => $responseTxt,
            'updated_ip' => Utils::ipAddress(),
            'updated_at' => date('Y-m-d H:i:s', time())
        ]);

        return ($res > 0) ? true : false;
    }

    /**
     * 根据订单号获取uid
     *
     * @param $orderId
     * @return mixed
     */
    public static function getUserOrderUid($orderId)
    {
        return UserOrder::where(['orderid' => $orderId])->value('user_id');
    }

    /**
     * 创建订单
     *
     * @param array $data
     * @return mixed
     */
    public static function createOrder($data = [])
    {
        return UserOrder::updateOrCreate($data);
    }

}