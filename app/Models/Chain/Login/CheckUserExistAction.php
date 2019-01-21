<?php

namespace App\Models\Chain\Login;

use App\Models\Orm\UserAuth;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Login\CheckCodeAction;
use App\Models\Chain\Login\CheckPasswordAction;
use DB;

class CheckUserExistAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '该用户不存在!', 'code' => 1);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /*  第一步:检查用户是否存在
     * @return array
     */

    public function handleRequest()
    {
        if ($this->checkUserExist($this->params['mobile']) == true)
        {
            $this->setSuccessor(new CheckUserLockAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 查数据库确认用户信息是否存在
     */
    private function checkUserExist($mobile)
    {
        return UserAuth::select("mobile")->where('mobile', '=', $mobile)->first();
    }

}
