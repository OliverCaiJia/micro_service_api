<?php

namespace App\Models\Chain\FastLogin;

use App\Models\Factory\AuthFactory;
use App\Models\Factory\UserFactory;
use App\Models\Chain\AbstractHandler;

class FetchUserInfoAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '用户登录失败!!', 'code' => 9004);
    protected $data;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /* * 返回个人信息、返回男女、是否显示选择身份页面
     * @return array
     */

    public function handleRequest()
    {
        if ($this->getUserInfo($this->params) == true)
        {
            return $this->data;
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 查数据库返回个人信息以及性别
     * @param $params
     */
    private function getUserInfo($params)
    {
        $info = AuthFactory::fetchUserInfo($params['sd_user_id']);
        if ($info)
        {
            $info['flag'] = $info['indent'] > 0 ? 1 :0;

            $this->data = $info;
            return true;
        }
        return false;
    }

}
