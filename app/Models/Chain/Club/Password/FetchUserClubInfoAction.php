<?php

namespace App\Models\Chain\Club\Password;

use App\Models\Chain\AbstractHandler;

class FetchUserClubInfoAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '返回论坛用户信息出错', 'code' => 1003);
    protected $datas = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第四步:返回论坛用户数据 直接登录
     * @return array
     */

    public function handleRequest()
    {
        if ($this->fetchUserClubInfo($this->params) == true) {
            return $this->datas;
        } else {
            return $this->error;
        }
    }

    /**
     * 第四步:返回论坛用户数据 直接登录
     */
    private function fetchUserClubInfo($params = [])
    {
        $datas = $params['club'];
        $userClub = [];
        $userClub['club_user_id'] = $datas['club_user_id'];
        $userClub['username'] = $datas['username'];
        $userClub['mobile'] = $datas['phone'];
        $userClub['redirect_uri'] = empty($datas['redirect_uri']) ? '' : $datas['redirect_uri'];
        $userClub['uia'] = $datas['uia'];

        $this->datas = $userClub ? $userClub : [];

        return $userClub;

    }

}
