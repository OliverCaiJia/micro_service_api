<?php

namespace App\Models\Chain\UserShadow;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ShadowFactory;
use App\Models\Chain\UserShadow\CreateShadowLogAction;

class CheckUserShadowAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '马甲用户已存在！', 'code' => 1002);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     *   3.判断表sd_user_shadow中是否存在唯一的shadow_id与user_id(马甲是否注册)
     *   存在 已注册 返回true
     *   否则 继续
     */
    public function handleRequest()
    {
        if ($this->checkUserShadow($this->params) == true) {
            $this->setSuccessor(new CreateShadowLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**判断表sd_user_shadow中是否存在唯一的shadow_id与user_id
     * @param $params
     * @return bool
     */
    private function checkUserShadow($params)
    {
        $check = ShadowFactory::checkUserShadow($params);
        if ($check) {
            return false;
        }

        return true;
    }


}
