<?php

namespace App\Models\Chain\UserShadow;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\ShadowFactory;

class CreateUserShadowAction extends AbstractHandler
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
     * 4.插入sd_user_shadow
     */
    public function handleRequest()
    {
        if ($this->createShadow($this->params) == true) {
            // 更新sd_shadow_count表中注册总量
            $this->setSuccessor(new UpdateShadowCountAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    private function createShadow($params)
    {
        //向表sd_user_shadow中插入数据
        $userShadow = ShadowFactory::createUserShadow($params);
        if (!$userShadow)
        {
            return false;
        }

        return true;
    }

}