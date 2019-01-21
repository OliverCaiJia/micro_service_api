<?php

namespace App\Models\Chain\UserIdentity\IdcardFront;

use App\Constants\UserIdentityConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserinfoFactory;
use App\Strategies\UserIdentityStrategy;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 4.修改实名认证表
 */
class UpdateUserRealnameAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '实名认证记录失败', 'code' => 10006);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 4.修改实名认证表
     */
    public function handleRequest()
    {
        if ($this->updateUserRealname($this->params) == true) {
            return $this->data;
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     * 4.修改实名认证表
     */
    private function updateUserRealname($params = [])
    {
        $params['profile_id'] = UserinfoFactory::fetchProfileIdByUserId($params['userId']);
        $params['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FACE;
        $params['certificate_type'] = 0;
        // 修改实名认证表
        $realname = UserIdentityFactory::createOrUpdateUserRealnameByFront($params);
        //返回数据
        if (!$realname) {
            return false;
        }
        $returnfaceinfo = UserIdentityStrategy::getFaceToIdcardInfo($params);
        $this->data = $returnfaceinfo;

        return $returnfaceinfo;
    }

}
