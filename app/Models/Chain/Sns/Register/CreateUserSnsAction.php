<?php

namespace App\Models\Chain\Sns\Register;

use App\Models\Chain\AbstractHandler;
//use App\Models\Chain\Sns\Register\;
use App\Models\Factory\UserSnsFactory;

class CreateUserSnsAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => 'sd_user_opensns添加数据失败!', 'code' => 1002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第二步:添加sd_user_opensns用户信息
     * @return array
     */
    public function handleRequest()
    {
        if ($this->createUserSns($this->params) == true)
        {
            return true;
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 添加sd_user_opensns用户数据
     */
    private function createUserSns($params = [])
    {
        return UserSnsFactory::createUserSns($params);
    }
}
