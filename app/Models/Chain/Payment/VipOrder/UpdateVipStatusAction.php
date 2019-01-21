<?php

namespace App\Models\Chain\Payment\VipOrder;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PaymentFactory;
use App\Strategies\UserVipStrategy;

class UpdateVipStatusAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '更新vip状态失败！', 'code' => 1004);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第三步:更新vip状态
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updateVipStatus($this->params)) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     * 更新订单状态
     */
    private function updateVipStatus($params = [])
    {
        //更新会员状态的时间
        $uid = PaymentFactory::getUserOrderUid($params['orderid']);
        //根据返回的状态进行vip状态对应
        $vipStatus = UserVipStrategy::getVipStatus($params['status']);
        $res = PaymentFactory::updateUserVIPStatus($uid, $vipStatus, $params['vip_type']);

        return $res;
    }

}
