<?php

namespace App\Models\Chain\VipOrder;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PaymentFactory;

class UpdateOrderStatusAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '状态更新失败！', 'code' => 1003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第二步:更新订单状态
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updateOrderStatus($this->params)) {
            $this->setSuccessor(new UpdateVipStatusAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 更新订单状态
     */
    private function updateOrderStatus($params = [])
    {
        //更新订单状态的时间
        $res = PaymentFactory::updateUserOrderStatus($params);

        return $res;
    }

}
