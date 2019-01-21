<?php

namespace App\Strategies;

use App\Constants\PaymentConstant;
use App\Constants\UserReportConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\Chain\Order\ReportOrder\DoReportOrderLogicHandler;
use App\Models\Chain\Order\VipOrder\DoVipOrderLogicHandler;
use App\Models\Chain\Payment\ReportOrder\DoReportOrderHandler;
use App\Models\Chain\Payment\VipOrder\DoVipOrderHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Models\Orm\UserVip;
use App\Services\AppService;
use App\Services\Core\Payment\PaymentService;

/**
 * payment
 *
 * @package App\Strategies
 */
class PaymentStrategy extends AppStrategy
{

    /**
     * 根据不同的订单回调地址参数不同
     *
     * @param $type
     * @return string
     */
    public static function getDiffOrderCallback($type)
    {
        switch ($type) {
            case UserVipConstant::ORDER_TYPE:
                $param = PaymentStrategy::getUrlParams(UserVipConstant::VIP_TYPE_NID);
                break;
            case UserReportConstant::REPORT_ORDER_TYPE:
                //添加一些参数
                $param = "";
                break;
            default:
                $param = "";
        }

        return $param;
    }

    /**
     * 对支付回调地址添加,vip类型参数
     *
     * @param $vipTypeNid
     * @return string
     */
    public static function getUrlParams($vipTypeNid)
    {
        switch ($vipTypeNid) {
            case UserVipConstant::VIP_TYPE_NID:
                $str = '&vip_type=' . $vipTypeNid;
                break;
            default:
                $str = '&vip_type=' . UserVipConstant::VIP_TYPE_NID;
        }

        return $str;
    }

    /**
     * 根据不同的订单类型处理的责任链不同
     *
     * @param $order
     * @return mixed
     */
    public static function getDiffOrderTypeChain($order)
    {
        switch ($order['type']) {
            case UserVipConstant::ORDER_TYPE:
                $chain = new DoVipOrderLogicHandler($order);
                $result = $chain->handleRequest();
                break;
            case UserReportConstant::REPORT_ORDER_TYPE:
                $chain = new DoReportOrderLogicHandler($order);
                $result = $chain->handleRequest();
                break;
            default:
                $result = ['error' => RestUtils::getErrorMessage(1139), 'code' => 1139];
        }

        return $result;
    }

    /**
     * 根据不同的类型，处理不同的责任链
     *
     * @param $params
     * @return mixed
     */
    public static function getDiffOrderChain($params)
    {
        switch ($params['type']) {
            case UserVipConstant::ORDER_TYPE:
                $chain = new DoVipOrderHandler($params);
                $result = $chain->handleRequest();
                break;
            case UserReportConstant::REPORT_ORDER_TYPE:
                $chain = new DoReportOrderHandler($params);
                $result = $chain->handleRequest();
                break;
            default:
                $chain = new DoVipOrderHandler($params);
                $result = $chain->handleRequest();
        }

        return $result;
    }

    /**
     * 生成订单参数
     *
     * @param array $data
     * @return array
     */
    public static function orderYibaoParams($data = [], $params = [])
    {
        return [
            'orderid' => $data['order_id'],
            'transtime' => time(),
            'amount' => $params['amount'],
            'productcatalog' => '1',
            'productname' => $params['productname'],
            'productdesc' => $params['productdesc'],
            'identitytype' => 2,//用户id
            'identityid' => $data['user_id'],
            'terminaltype' => 0,
            'terminalid' => $data['terminal_id'],
            'userip' => Utils::ipAddress(),
            'directpaytype' => $data['pay_type'],
            'userua' => UserAgent::i()->getUserAgent(),
            'fcallbackurl' => AppService::YIBAO_CALLBACK_URL . AppService::API_URL_YIBAO_SYN . $params['url_params'] . PaymentStrategy::getDiffOrderCallback($params['url_params']),
            'callbackurl' => AppService::YIBAO_CALLBACK_URL . AppService::API_URL_YIBAO_ASYN . $params['url_params'] . PaymentStrategy::getDiffOrderCallback($params['url_params']),
            'orderexpdate' => $data['order_expired_time'],
            'cardno' => $data['cardno'],
            'idcardtype' => '01',
            'idcard' => $data['idcard'],
            'owner' => $data['owner'],
        ];
    }

    /**
     * 用户订单表的字段封装
     *
     * @param array $data
     * @param array $params
     * @return array
     */
    public static function getUserOrderParams($data = [], $params = [])
    {
        return [
            'user_id' => $data['user_id'],
            'bank_id' => isset($data['bank_id']) ? $data['bank_id'] : 0,
            'orderid' => $data['order_id'],
            'payment_order_id' => $data['payment_order_id'],
            'order_expired' => $data['order_expired'],  //订单有效期
            'order_type' => $params['order_type'],//订单类型
            'payment_type' => $params['payment_type'],//支付类型
            'pay_type' => $data['pay_type'],
            'terminaltype' => PaymentConstant::YIBAO_TERMINAL_TYPE,
            'terminalid' => $data['terminal_id'],
            'card_num' => $data['card_num'],
            'amount' => $params['amount'],//支付金额
            'user_agent' => UserAgent::i()->getUserAgent(),
            'created_ip' => Utils::ipAddress(),
            'created_at' => date('Y-m-d H:i:s'),
            'request_text' => $data['request_text'],
        ];
    }

    /**
     * 根据订单状态返回不同的信息
     *
     * @param array $params
     * @return mixed
     */
    public static function getDiffTypeInfo($params = [])
    {
        $type = isset($params['orderType']) ? $params['orderType'] : '';
        //会员信息 不是会员信息为空
        $message = isset($params['message']) ? $params['message'] : [];
        //vip_default
        $vipType = isset($params['vipNid']) ? $params['vipNid'] : '';

        switch ($type) {
            case UserVipConstant::ORDER_TYPE:
                $vip = UserVipFactory::getReVipAmount(UserVipConstant::VIP_TYPE_NID);
                //根据不同类型处理不同的数据
                $data = UserVipStrategy::getDiffVipTypeDeal($message, $vipType);
                //支付开始时间
                $today = empty($message) ? date('Y.m.d') : date('Y.m.d', strtotime($message['end_time']));
                //支付结束时间
                $lastDay = empty($data) ? date('Y.m.d', UserVipStrategy::getVipExpired()) : date('Y.m.d', strtotime($data['time']));
                $reList['price'] = $vip;
                $reList['price_twice'] = floatval($vip);
                $reList['business_name'] = UserVipConstant::ORDER_DEALER_NAME;
                $reList['bug_name'] = UserVipConstant::ORDER_PRODUCT_NAME;
                $reList['expired_time'] = $today . ' - ' . $lastDay;
                $reList['wechat'] = PaymentConstant::PAYMENT_YIBAO_WECHAT_PAY_STATUS;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                break;
            case UserReportConstant::REPORT_ORDER_TYPE:
                $report = UserReportFactory::fetchReportPrice();
                $reList['price'] = $report;
                $reList['price_twice'] = floatval($report);
                $reList['business_name'] = UserReportConstant::REPORT_MEMBER_NAME;
                $reList['bug_name'] = UserReportConstant::REPORT_ORDER_PRODUCT_NAME;
                $reList['expired_time'] = UserReportConstant::REPORT_PRODUCT_VALIDITY;
                $reList['wechat'] = PaymentConstant::PAYMENT_YIBAO_WECHAT_PAY_STATUS;
                $reList['alipay'] = PaymentConstant::PAYMENT_YIBAO_ALI_PAY_STATUS;
                break;
            default:
                $reList = [];
        }

        return $reList;
    }

    /**
     * 获取订单相同的部分
     *
     * @param $order
     * @param $bankCardInfo
     * @return mixed
     */
    public static function getOrderSameSection($order, $bankCardInfo = [])
    {
        //获取用户信息，银行卡，银行id
        $userAuthInfo = UserIdentityFactory::fetchIdcardAuthenInfo($order['user_id']);

        $account = isset($bankCardInfo['account']) ? $bankCardInfo['account'] : '';
        $order['idcard'] = isset($userAuthInfo['certificate_no']) ? $userAuthInfo['certificate_no'] : '';
        $order['owner'] = isset($userAuthInfo['realname']) ? $userAuthInfo['realname'] : '';
        $order['cardno'] = ($order['pay_type'] == 3) ? $account : '';
        $order['order_id'] = PaymentService::i()->generateOrderId(); //TODO:生成orderid
        $order['order_expired_time'] = PaymentConstant::ORDER_EXPIRED_MINUTE;
        //订单过期显示的时间
        $order['order_expired'] = date('Y-m-d H:i:s', strtotime("+{$order['order_expired_time']} minutes"));
        $order['card_num'] = !empty($account) ? $account : '';

        return $order;
    }

    /**
     * 获取会员编号
     *
     * @param int $lastId 最后一个ID
     * @param string $prefix 前缀
     * @param string $name 名称
     * @param int $num 编号数字
     * @return string
     */
    public static function generateId($lastId, $name = 'VIP', $prefix = 'SD', $num = 8)
    {
        //获取毫秒时间
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec * 1000);
        $millisecond = str_pad($msec, 3, '0', STR_PAD_RIGHT);
        $timeLength = date("YmdHis") . $millisecond;

        $length = PaymentConstant::PAYMENT_PRODUCT_NUMBER_LENGTH - strlen(trim($prefix)) - strlen(trim($name)) - strlen(trim($timeLength)) - $num - 2;

        //如果还有多余的长度获取随机字符串
        $str = '';
        if ($length > 0) {
            $str = PaymentService::i()->getRandString($length);
        } else {
            $name = substr($name, 0, $length);
        }

        //获取数字
        $strNum = sprintf("%0" . $num . "d", ($lastId + 1)); //UserVipFactory::getVipLastId()

        return $prefix . '-' . $name . '-' . $str . $timeLength . $strNum;
    }

    /**
     * 生成前端报告编号
     *
     * @param $lastId
     * @param string $prefix
     * @param int $num
     * @return string
     */
    public static function generateFrontId($lastId, $prefix = 'SD', $num = 3)
    {
        $timeLenght = date('ymd', time());

        //获取数字
        $strNum = sprintf("%0" . $num . "d", ($lastId + 1)); //UserVipFactory::getVipLastId()

        return $prefix . $timeLenght . $strNum;
    }

}