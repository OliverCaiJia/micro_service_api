<?php

namespace App\Models\Chain\Club\Password;

use App\Models\Chain\AbstractHandler;
use App\Services\Core\Club\ClubService;

class ClubPasswordAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '论坛修改密码失败！', 'code' => 1001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第二步:调论坛修改密码接口获取返回值
     * @return array
     */
    public function handleRequest()
    {
        if ($this->clubPassword($this->params)) {
            $this->setSuccessor(new UpdateUserClubPasswordAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 调论坛修改密码接口获取返回值
     */
    private function clubPassword($params = [])
    {
        //数据规整
        $datas['uia'] = $params['club']['uia'];
        $datas['old_password'] = $params['club']['club_password'];
        $datas['club_user_id'] = $params['club']['club_user_id'];
        $datas['new_password'] = $params['new_password'];
        //调论坛修改密码接口获取返回值
        $passwordData = ClubService::clubPassword($datas);
        if ($passwordData['code'] != 0)
        {
            $this->error['code'] = $passwordData['code'];
            $this->error['error'] = $passwordData['msg'];
            return false;
        }
        return $passwordData['data'];
    }

}
