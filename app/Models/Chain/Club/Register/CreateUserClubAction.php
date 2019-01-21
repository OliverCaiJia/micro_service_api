<?php

namespace App\Models\Chain\Club\Register;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Club\Register\FetchUserClubInfoAction;
use App\Models\Factory\ClubFactory;

class CreateUserClubAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '注册成功，向sd_user_club表中插入数据有问题！', 'code' => 1002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第二步:将返回值添加到sd_user_club中
     * @return array
     */
    public function handleRequest()
    {
        if ($this->createUserClub($this->params) == true)
        {
            $this->setSuccessor(new FetchUserClubInfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    /**
     * 将返回值添加到sd_user_club中
     */
    private function createUserClub($params = [])
    {
        $datas = $params['club'];

        //在sd_user_club表中插入数据
        $userClub = ClubFactory::createUserClub($datas);

        if ($userClub)
        {
            return true;
        }
        return false;
    }

}
