<?php

namespace App\Models\Chain\Order\ReportOrder;

use App\Constants\UserReportConstant;
use App\Constants\UserVipConstant;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserOrderFactory;
use App\Models\Factory\UserReportFactory;
use App\Services\AppService;
use App\Services\Core\Payment\PaymentService;
use App\Strategies\PaymentStrategy;
use App\Strategies\UserReportStrategy;
use App\Strategies\UserVipStrategy;

class CreateReportOrderAction extends AbstractHandler
{

    private $params = array();
    private $backInfo = array();
    protected $error = array('error' => '创建报告订单失败！', 'code' => 1001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第一步:创建订单
     * @return array
     */
    public function handleRequest()
    {
        if ($this->createReportOrder($this->params)) {
            $this->setSuccessor(new BankCardAction($this->params, $this->backInfo));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 创建报告订单
     *
     * @param array $params
     * @param array $vip
     * @return bool
     */
    private function createReportOrder($params = [])
    {
        //如果是银行卡支付
        $bankCardInfo = [];
        if ($params['pay_type'] == 3)
        {
            $bankCardInfo = UserBankCardFactory::getBankCardInfo($params['bankcard_id'], $params['user_id']);
            if (!$bankCardInfo)
            {
                $this->error = ['error' => RestUtils::getErrorMessage(1135), 'code' => 1135];
                return false;
            }
            $params['bank_id'] = $bankCardInfo['bank_id'];
        }

        $params = PaymentStrategy::getOrderSameSection($params, $bankCardInfo);

        //调用易宝订单
        $otherParams = UserReportStrategy::getYibaoOtherParams();
        $yiBaoparams = PaymentStrategy::orderYibaoParams($params, $otherParams);//UserVipStrategy::orderYibaoParams($order);
        $ret = PaymentService::i($params['shadow_nid'])->order($yiBaoparams);

        if (empty($ret)) {
            $this->error = ['error' => RestUtils::getErrorMessage(1136), 'code' => 1136];
            return false;
        }

        $params['request_text'] = json_encode($yiBaoparams, JSON_UNESCAPED_UNICODE);
        $params['payment_order_id'] = $ret['yborderid'];
        $params['orderId'] = $ret['orderid'];
        $back['payurl'] = $ret['payurl'];
        $back['fcallbackurl'] = AppService::YIBAO_CALLBACK_URL . AppService::API_URL_YIBAO_SYN . UserReportConstant::REPORT_ORDER_TYPE . PaymentStrategy::getDiffOrderCallback(UserReportConstant::REPORT_ORDER_TYPE);

        //创建订单
        $orderOtherParams = UserReportStrategy::getUserOrderOtherParams();
        $reOrder = PaymentStrategy::getUserOrderParams($params, $orderOtherParams);
        $createOrder = PaymentFactory::createOrder($reOrder);
        if(!$createOrder)
        {
            $this->error = ['error' => RestUtils::getErrorMessage(1138), 'code' => 1138];
            return false;
        }
        //将返回结果赋值
        $this->backInfo = $back;

        return true;
    }

}
