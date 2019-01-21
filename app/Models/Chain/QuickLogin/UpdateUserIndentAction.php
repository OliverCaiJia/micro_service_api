<?php

namespace App\Models\Chain\QuickLogin;

use App\Models\Factory\AuthFactory;
use App\Models\Factory\UserFactory;
use App\Models\Chain\AbstractHandler;

class UpdateUserIndentAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '用户登录失败!!', 'code' => 9004);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /*
    * 更新用户身份信息
     * @return array
     */

    public function handleRequest()
    {
        if ($this->updateUserIndent($this->params) == true)
        {
	        $this->setSuccessor(new FetchUserInfoAction($this->params));
	        return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return true;
        }
    }

    /**
     * 设置用户身份
     * @param $params
     */
    private function updateUserIndent($params)
    {
       #更新sd_user_auth中的indent 条件是indent为0的时候
	    AuthFactory::updateUserAuthIndent($params['sd_user_id'], 2, true);
        #更新sd_user_indenty表中的indent
        AuthFactory::updateUserIdentity($params['sd_user_id'], 2, true);
        return true;
    }

}
