<?php

namespace App\Models\Chain\Order\VipOrder;

use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserVipFactory;
use App\Services\Core\Payment\YiBao\YiBaoService;

class CheckVipStatusAction extends AbstractHandler
{

    private $params = array();
    private $vip = array();
    protected $error = array('error' => '检查会员状态失败！', 'code' => 1002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第一步:获取回调的传参数数据
     * @return array
     */
    public function handleRequest()
    {
        if ($this->isVip($this->params))
        {
            $this->setSuccessor(new CreateVipAction($this->params, $this->vip));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 是否是会员
     */
    private function isVip($params = [])
    {
        $vips['vip_type'] = UserVipFactory::getReVipTypeId(UserVipConstant::VIP_TYPE_NID);
        if(empty($vips['vip_type']))
        {
            $this->error = ['error' => RestUtils::getErrorMessage(1140), 'code' => 1140];
            return false;
        }

        $vipInfo = UserVipFactory::getVIPInfo($params['user_id'], $vips['vip_type']);
//        if ($vipInfo) {
//            $this->error = ['error' => RestUtils::getErrorMessage(1134), 'code' => 1134];
//            return false;
//        }
        //将vip信息传入数组中
        $this->vip = $vips;

        return true;
    }

}
