<?php

namespace App\Models\Chain\UserIdentity\FaceAlive;

use App\Constants\UserIdentityConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Strategies\UserIdentityStrategy;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 6.返回用户认证之后信息
 */
class FetchUserinfoAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '身份证信息返回错误！', 'code' => 10004);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 返回用户认证之后信息
     */
    public function handleRequest()
    {
        if ($this->fetchUserinfo($this->params) == true) {
            return $this->data;
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array
     * 返回用户认证之后信息
     */
    private function fetchUserinfo($params = [])
    {
        //活体认证成功返回用户信息
        //证件类型
        $params['certificate_type'] = UserIdentityConstant::CERTIFICATE_TYPE_IDCARD;
        $returnfaceinfo = UserIdentityStrategy::getFaceAliveToIdcardInfo($params);
        $this->data['info'] = $returnfaceinfo;
        //SLogger::getStream()->info('认证信息', $this->data);

        return $returnfaceinfo;
    }

}
