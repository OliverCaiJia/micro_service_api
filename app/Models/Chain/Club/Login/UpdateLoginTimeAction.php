<?php

namespace App\Models\Chain\Club\Login;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ClubFactory;
use App\Models\Chain\Club\Login\FetchUserClubInfoAction;

class UpdateLoginTimeAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '时间修改出错！', 'code' => 1003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第二步:同步 sd_user_club 表中时间
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updateLoginTime($this->params) == true) {
            $this->setSuccessor(new FetchUserClubInfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 同步 sd_user_club 表中时间
     */
    private function updateLoginTime($params = [])
    {
        //更新sd_user_club的时间
        return $userClub = ClubFactory::updateLoginTime($params['club']);
    }

}
