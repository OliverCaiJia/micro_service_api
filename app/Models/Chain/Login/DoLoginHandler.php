<?php

namespace App\Models\Chain\Login;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Login\CheckUserExistAction;

class DoLoginHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 第一步:检查该用户信息是否存在
     * 第二步:检查用户是否被锁定
     * 第三步:检查该用户密码是否正确
     * 第四步:更新用户最后登录时间
     * 第五步:刷新用户token
     * 第六步:返回用户信息以及性别、真实性别
     *
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $this->setSuccessor(new CheckUserExistAction($this->params));
        return $this->getSuccessor()->handleRequest();
    }

}
