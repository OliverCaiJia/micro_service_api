<?php

namespace App\Models\Chain\FastRegister;

use App\Helpers\Generator\TokenGenerator;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\FastRegister\FetchUserInfoAction;
use App\Models\Factory\UserFactory;

class UpdatePasswordAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '设置密码失败!', 'code' => 9001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /** 检查是否设置密码 随机生成密码
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updatePassword($this->params) == true) {
            $this->setSuccessor(new FetchUserInfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return true;
        }
    }

    /**
     * 检查是否设置密码 随机生成密码
     * @param $params
     * @return mixed
     */
    private function updatePassword($params)
    {
        //随机生成密码
        $params['password'] = md5(TokenGenerator::generateToken(8));
        //修改密码
        $updatePwd = UserFactory::updatePasswordById($params);
        if($updatePwd) {
            UserFactory::setUserActivated($params['sd_user_id']);
        }
        return true;
    }

}
