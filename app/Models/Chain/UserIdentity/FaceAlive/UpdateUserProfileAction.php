<?php

namespace App\Models\Chain\UserIdentity\FaceAlive;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserinfoFactory;
use App\Models\Chain\UserIdentity\FaceAlive\FetchUserinfoAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 5.同步到表sd_user_profile中修改身份证信息
 */
class UpdateUserProfileAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '同步到表sd_user_profile中修改身份证信息失败', 'code' => 10003);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 同步到表sd_user_profile中修改身份证信息
     */
    public function handleRequest()
    {
        if ($this->updateUserRealname($this->params) == true) {
            $this->setSuccessor(new FetchUserinfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     * 同步到表sd_user_profile中修改身份证信息
     */
    private function updateUserRealname($params = [])
    {
        $profile = UserinfoFactory::updateProfileById($params);

        return $profile;
    }

}
