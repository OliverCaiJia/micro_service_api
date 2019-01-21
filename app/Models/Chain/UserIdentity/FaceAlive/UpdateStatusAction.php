<?php

namespace App\Models\Chain\UserIdentity\FaceAlive;

use App\Constants\UserIdentityConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Chain\UserIdentity\FaceAlive\UpdateUserProfileAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 2.修改活体认证状态
 */
class UpdateStatusAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '修改状态失败！', 'code' => 10002);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 修改活体认证状态
     */
    public function handleRequest()
    {
        if ($this->updateUserRealname($this->params) == true) {
            $this->setSuccessor(new UpdateUserProfileAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 修改活体认证状态
     */
    private function updateUserRealname($params = [])
    {
        //修改sd_user_alive状态
        $params['alive_status'] = 1;
        $alive = UserIdentityFactory::updateAliveStatusById($params);
        //修改sd_user_realnem status = 9
        $params['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FINAL;
        $realname = UserIdentityFactory::updateStatusById($params);
        //备份身份证号
        $certificateBackup = UserIdentityFactory::updateCertificateBackup($params);
        if (!$alive || !$realname || !$certificateBackup) {
            return false;
        }

        return true;
    }

}
