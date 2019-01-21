<?php

namespace App\Services\Core\Payment;

use App\Models\Factory\PaymentFactory;
use App\Services\AppService;
use App\Services\Core\Payment\YiBao\YiBaoService;

class PaymentService extends AppService
{
    //订单号长度
    const PAYMENT_ORDERID_LENGTH = 32;

	public static $services;
	public static $nid;

	public static function i($shadowNid = 'sfwer')
	{

		if (!(self::$services instanceof static))
		{
            $paymentId = PaymentFactory::getPaymentConfig($shadowNid);
			self::$services = new static();
			self::$nid = PaymentFactory::getPaymentNid($paymentId);
		}

		return self::$services;
	}

    /**
     * 下单
     *
     * @param array $params
     * @return string
     */
    public function order($params)
    {
        switch (self::$nid)
        {
            case 'YBZF':
                $re = YiBaoService::i()->orderPay($params);
                break;
            default:
                $re = YiBaoService::i()->orderPay($params);
                break;
        }

        return $re;
    }

    /**
     * 订单查询
     *
     * @param array $params
     * @return array|mixed|string
     */
    public function orderQuery($params)
    {
        switch (self::$nid)
        {
            case 'YBZF':
                $re = YiBaoService::i()->orderQuery($params);
                break;
            default:
                $re = YiBaoService::i()->orderQuery($params);
                break;
        }

        return $re;
    }

    /**
     * 订单退款
     *
     * @param array a$params
     * @return array|mixed|string
     */
    public function orderRefund($params)
    {
        switch (self::$nid)
        {
            case 'YBZF':
                $re = YiBaoService::i()->orderRefund($params);
                break;
            default:
                $re = YiBaoService::i()->orderRefund($params);
                break;
        }

        return $re;
    }

    /**
     * 额外提供的接口信息
     *
     * @param array $params
     * @return array|mixed|string
     */
    public function extraInterface($params)
    {
        switch (self::$nid)
        {
            case 'YBZF':
                $re = YiBaoService::i()->bankCardInfo($params);
                break;
            default:
                $re = YiBaoService::i()->bankCardInfo($params);
                break;
        }

        return $re;
    }

    /**
     * 生成订单号
     *
     * @param string $prefix
     * @param string $paymentName
     * @return string
     */
    public function generateOrderId($prefix = 'SD',$paymentName = 'YIBAO')
    {
        //获取毫秒时间
        list($usec, $sec) = explode(" ", microtime());
        $msec=round($usec*1000);
        $millisecond = str_pad($msec,3,'0',STR_PAD_RIGHT);
        $timeLength = date("YmdHis").$millisecond;

        //计算随机生成多长的字符串
        $length = PaymentService::PAYMENT_ORDERID_LENGTH - strlen(trim($prefix)) - strlen(trim($paymentName)) - strlen(trim($timeLength)) - 2;
        $orderId = $prefix . '-' . $paymentName . '-' .$this->getRandString($length) . $timeLength;

        return $orderId;
    }

    /**
     * 随机生成大写字母和数字的字符串
     *
     * @param int $length
     * @return string
     */
    public function getRandString($length = 7)
    {
        $baseString = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randString = '';
        $_len = strlen($baseString);
        for($i=1;$i<=$length;$i++){
            $randString .= $baseString[rand(0, $_len-1)];
        }

        return $randString;
    }

}