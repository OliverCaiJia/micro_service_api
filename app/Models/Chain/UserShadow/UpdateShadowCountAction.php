<?php

namespace App\Models\Chain\UserShadow;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ShadowFactory;

class UpdateShadowCountAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '修改马甲注册数量失败！', 'code' => 1004);
    protected $data;

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * @return array|bool
     * 5.更新sd_shadow_count表中注册总量
     */
    public function handleRequest()
    {
        if ($this->updateShadowCount($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }


    private function updateShadowCount($params)
    {
        $count = ShadowFactory::updateShadowCount($params['shadow_nid']);
        if (!$count) {
            return false;
        }
        return true;
    }

}




