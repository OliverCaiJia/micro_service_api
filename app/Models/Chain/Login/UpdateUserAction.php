<?php

namespace App\Models\Chain\Login;

use App\Models\Factory\UserFactory;
use App\Models\Orm\UserAuth;
use App\Models\Orm\UserProfile;
use App\Models\Chain\AbstractHandler;
use App\Strategies\UserStrategy;
use \DB;

class UpdateUserAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '对不起,用户登录失败！', 'code' => 111);
    protected $data;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 更新用户最后登录时间、返回个人信息、返回男女、是否显示选择身份页面
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateUser($this->params) == true)
        {
            return $this->data;
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 更新用户最后登录时间
     * @param $params
     * @return bool
     */
    private function updateUser($params)
    {
        $userList = UserFactory::updateLoginTime($params['sd_user_id']);
        if ($userList)
        {
            $userData = [
                'sd_user_id' => $params['sd_user_id'],
                'mobile' => $params['mobile'],
                'username' => $params['username'],
                'indent' => $params['indent'],
                'accessToken' => $params['accessToken']
            ];
            //返回性别以及真实姓名
            $userProfile = UserFactory::sex($params['sd_user_id']);
            $userData = array_merge($userData, $userProfile);
            //是否显示选择身份页面
            $display = UserStrategy::fetchDisplay($userList['last_login_time']);
            $userData['display'] = $display['display'];
            
            $user = UserFactory::getUserById($userData['sd_user_id']);
            $userData['accessToken'] = $user->accessToken;

            $this->data = $userData;
            return true;
        }
        return false;
    }

}
