<?php

namespace App\Models\Chain\UserIdentity\Alive;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserIdentityFactory;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 *
 */
class UpdateUserAliveAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '修改sd_user_alive表失败', 'code' => 10004);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateUserAlive($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     *
     */
    private function updateUserAlive($params = [])
    {
        //创建活体认证信息
        $params['alive_status'] = 0;
        $alive = UserIdentityFactory::createOrUpdateUserAlive($params);

        return $alive;
    }

}
