<?php

namespace App\Models\Chain\UserIdentity\IdcardBack;

use App\Constants\UserIdentityConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserinfoFactory;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 4.修改实名认证表
 */
class UpdateUserRealnameAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '实名认证记录失败', 'code' => 10004);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array|bool
     * 4.修改实名认证表
     */
    public function handleRequest()
    {
        if ($this->updateUserRealname($this->params) == true) {
            return true;
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
        $params['status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FACE;
        $params['certificate_type'] = 0;
        $params['profile_id'] = UserinfoFactory::fetchProfileIdByUserId($params['userId']);
        // 修改实名认证表
        $realname = UserIdentityFactory::createOrUpdateUserRealnameByBack($params);
        //返回数据
        if(!$realname) {
            return false;
        }

        return true;
    }

}
