<?php

namespace App\Models\Chain\UserShadow;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ShadowFactory;
use App\Models\Chain\UserShadow\CheckUserShadowAction;
use Illuminate\Support\Facades\Log;

class CheckShadowNidAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => 'shadow_nid不存在', 'code' => 1001);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.判断shadow_nid 是否存在
     */
    public function handleRequest()
    {
        if ($this->checkShadowNid($this->params) == true) {
            // 2.判断sd_user_shadow表中是否存在唯一的shadow_id + user_id
            $this->setSuccessor(new CheckUserShadowAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**判断shadow_nid 是否存在,不存在 return false;
     * @param $params
     * @return bool
     */
    private function checkShadowNid($params)
    {
        if (empty($params['shadow_nid']))
        {
            return false;
        }

        //根据shadow_nid获取id
        if ($params['shadow_nid'] != 'sudaizhijia')
        {
            $id = ShadowFactory::fetchIdByShadowNid($params['shadow_nid']);
            if (empty($id))
            {
                return false;
            }

            $this->params['shadow_id'] = $id;
        } else {
            $this->params['shadow_id'] = 0;
        }

        return true;
    }


}
