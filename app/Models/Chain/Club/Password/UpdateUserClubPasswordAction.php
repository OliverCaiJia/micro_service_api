<?php

namespace App\Models\Chain\Club\Password;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ClubFactory;

class UpdateUserClubPasswordAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '修改用论坛关联表中的密码失败', 'code' => 1002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第三步:将sd_user_club表中密码进行修改
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updateUserClubPassword($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     * 将sd_user_club表中密码进行修改
     */
    private function updateUserClubPassword($params = [])
    {
        return ClubFactory::updateUserClubPwd($params);
    }

}
