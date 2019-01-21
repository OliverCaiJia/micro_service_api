<?php

namespace App\Models\Chain\Login;

use App\Models\Factory\AuthFactory;
use App\Models\Chain\AbstractHandler;

class UpdateLoginTimeAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '用户登录失败!', 'code' => 9001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /** 更新用户最后登录时间
     * @return array
     */

    public function handleRequest()
    {
        if ($this->updateLoginTime($this->params['sd_user_id']) == true)
        {
            $this->setSuccessor(new RenovateTokenAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }
	
	/**
	 * sd_user_auth数据表更新用户最后的登录时间
	 * @param $params
	 * @return mixed
	 */
    private function updateLoginTime($user_id)
    {
        return AuthFactory::updateLoginTime($user_id);
    }

}
