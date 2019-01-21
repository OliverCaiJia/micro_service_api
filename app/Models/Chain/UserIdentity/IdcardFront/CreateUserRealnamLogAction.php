<?php

namespace App\Models\Chain\UserIdentity\IdcardFront;

use App\Constants\UserIdentityConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Chain\UserIdentity\IdcardFront\UpdateUserRealnameAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 3.记实名认证流水
 */
class CreateUserRealnamLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '实名认证流水记录失败', 'code' => 10005);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 3.记实名认证流水
     */
    public function handleRequest()
    {
        if ($this->createUserRealnamLog($this->params) == true) {
            $this->setSuccessor(new UpdateUserRealnameAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 3.记实名认证流水
     */
    private function createUserRealnamLog($params = [])
    {
        // 记流水
        $params['type'] = 'faceid';
        $params['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FACE;
        $params['certificate_type'] = UserIdentityConstant::CERTIFICATE_TYPE_IDCARD;
        return UserIdentityFactory::createUserRealnameLog($params);

    }

}
