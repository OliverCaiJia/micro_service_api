<?php

namespace App\Models\Chain\Login;

use App\Models\Orm\UserAuth;
use App\Models\Chain\AbstractHandler;
use DB;
use App\Models\Factory\UserFactory;

class CheckPasswordAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '密码输入不正确！', 'code' => 9000);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 检查密码是否正确
     * @return array
     */
    public function handleRequest()
    {
        if ($this->checkPassword($this->params['mobile'], $this->params['password']) == true)
        {
            $this->setSuccessor(new UpdateLoginTimeAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 查数据库确认用户密码是否正确
     */
    private function checkPassword($mobile, $password)
    {
        #查库
        $user = UserAuth::select(DB::raw('sd_user_id, username, indent, accessToken'))->where('mobile', '=', $mobile)->where('password', '=', $password)->first();
        if ($user)
        {
            $user = $user->toArray();
            $this->params['sd_user_id'] = $user['sd_user_id'];
            $this->params['username'] = $user['username'];
            $this->params['indent'] = $user['indent'];
            $this->params['accessToken'] = $user['accessToken'];
            
            return true;
        }
        return false;
    }

}
