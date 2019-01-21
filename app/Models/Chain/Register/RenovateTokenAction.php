<?php

namespace App\Models\Chain\Register;

use App\Models\Factory\AuthFactory;
use App\Models\Factory\UserFactory;
use App\Models\Chain\AbstractHandler;
use App\Helpers\Generator\TokenGenerator;
use App\Models\Chain\Register\FetchUserInfoAction;
use Cache;
use Carbon\Carbon;

class RenovateTokenAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '更新token失败!!', 'code' => 9002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /*     * 刷新用户token
     * @return array
     */

    public function handleRequest()
    {
        if ($this->renovateToken($this->params) == true)
        {
            $this->setSuccessor(new FetchUserInfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * @param $params
     */
    private function renovateToken($params)
    {
        $user = UserFactory::getUserById($params['sd_user_id']);
        if ($user)
        {
            Cache::put('user_token_' . $params['sd_user_id'], $user, Carbon::now()->addDays(7));
	        return true;
        }
        return false;
    }

}
