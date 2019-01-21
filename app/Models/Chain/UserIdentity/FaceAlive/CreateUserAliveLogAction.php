<?php

namespace App\Models\Chain\UserIdentity\FaceAlive;

use App\Constants\UserIdentityConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Chain\UserIdentity\FaceAlive\UpdateStatusAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 3.活体认证流水
 */
class CreateUserAliveLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '活体认证流水错误！', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 活体认证流水
     */
    public function handleRequest()
    {
        if ($this->createUserAliveLog($this->params) == true) {
            $this->setSuccessor(new UpdateStatusAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 活体认证流水
     */
    private function createUserAliveLog($params = [])
    {
        //证件类型 身份证0
        $params['certificate_type'] = UserIdentityConstant::CERTIFICATE_TYPE_IDCARD;
        $this->params['certificate_type'] = $params['certificate_type'];
        //活体认证流水
        $log = UserIdentityFactory::createUserAliveLog($params);

        if (!$log) {
            return false;
        }
        return true;
    }

}
