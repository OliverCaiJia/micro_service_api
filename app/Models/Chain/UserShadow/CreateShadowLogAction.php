<?php

namespace App\Models\Chain\UserShadow;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\ShadowFactory;
use App\Models\Chain\UserShadow\UpdateShadowCountAction;
use App\Models\Orm\DeliveryCount;
use App\Models\Orm\UserDelivery;

class CreateShadowLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '创建马甲流水失败！', 'code' => 1003);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }


    /**
     * @return array
     * 3.不存在 插入sd_shadow_log
     */
    public function handleRequest()
    {
        if ($this->createShadowLog($this->params) == true) {
            $this->setSuccessor(new CreateUserShadowAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    private function createShadowLog($params)
    {
        $params['delivery_id'] = DeliveryFactory::fetchChannelId($params['channel_nid']);
        if(!$params['delivery_id']) {
            $params['delivery_id'] = DeliveryFactory::fetchChannelId('shadow_default');
        }
        //创建马甲流水
        $log = ShadowFactory::createShadowLog($params);
        if (!$log) {
            return false;
        }

        $this->params['delivery_id'] = $params['delivery_id'];
        return true;
    }

}