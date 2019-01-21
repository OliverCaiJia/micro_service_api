<?php

namespace App\Models\Chain\Club\Password;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ClubFactory;
use App\Models\Chain\Club\Password\ClubPasswordAction;

class CheckUserClubAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '验证速贷论坛关联表失败！', 'code' => 1001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第二步:验证sd_user_club表中是否存在  存在——修改  不存在——不修改
     * @return array
     */
    public function handleRequest()
    {
        if ($this->checkUserClub($this->params)) {
            $this->setSuccessor(new ClubPasswordAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            //sd_user_club表中不存在信息  则只修改速贷之家密码
            return true;
        }
    }

    /**
     * 验证sd_user_club表中是否存在  存在——修改  不存在——不修改
     */
    private function checkUserClub($params = [])
    {
        //查表 sd_user_club 判断是否存在论坛用户 并获取论坛用户的登录信息
        $userClub = ClubFactory::fetchUserClub($params['user_id']);
        $this->params['club'] = $userClub;
        return $userClub;
    }

}
