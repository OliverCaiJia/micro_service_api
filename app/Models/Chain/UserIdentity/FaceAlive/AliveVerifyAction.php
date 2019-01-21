<?php

namespace App\Models\Chain\UserIdentity\FaceAlive;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Services\Core\Validator\FaceId\FaceIdService;
use App\Models\Chain\UserIdentity\FaceAlive\CreateUserAliveLogAction;
use App\Strategies\UserIdentityStrategy;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 1.face++验证活体
 */
class AliveVerifyAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => 'face++验证活体失败', 'code' => 10001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * face++验证活体
     */
    public function handleRequest()
    {
        if ($this->aliveVerify($this->params) == true) {
            $this->setSuccessor(new CreateUserAliveLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * face++验证活体
     */
    private function aliveVerify($params = [])
    {
        $alive = FaceIdService::verify($params);

        if (isset($alive['error_message'])) {
            return false;
        }
        //活体认证返回错误信息
        $errorData = UserIdentityStrategy::getFaceidErrorMeg($alive);
        if (isset($errorData['error'])) {
            $this->error = $errorData;
            return false;
        }
        return true;

    }

}
