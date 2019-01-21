<?php

namespace App\Models\Chain\Order\VipOrder;

use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\UserVipFactory;
use App\Strategies\UserVipStrategy;

class CreateVipAction extends AbstractHandler
{

    private $params = array();
    private $vip = array();
    protected $error = array('error' => '创建vip失败！', 'code' => 1003);

    public function __construct($params, $vip)
    {
        $this->params = $params;
        $this->vip = $vip;
    }

    /**
     * 第三步:创建vip
     * @return array
     */
    public function handleRequest()
    {
        if ($this->createVip($this->params, $this->vip)) {
            $this->setSuccessor(new CreateVipOrderAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 创建vip
     *
     * @param array $params
     * @param array $vip
     * @return bool
     */
    private function createVip($params = [], $vip = [])
    {
        //添加或更改会员信息
        $vips['vip_type'] = $vip['vip_type'];
        $vips['user_id'] = $params['user_id'];
        $vips['vip_no'] = UserVipStrategy::generateId(UserVipFactory::getVipLastId());
        //$vips['end_time'] = UserVipStrategy::getVipExpired();
        $createVip = UserVipFactory::createVipInfo($vips);
        if (!$createVip) {
            $this->error = ['error' => RestUtils::getErrorMessage(1137), 'code' => 1137];
            return false;
        }

        return $createVip;
    }

}
