<?php

namespace App\Models\Chain\UserSign;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\UserSign\CreateCreditLogAction;
use App\Models\Orm\UserSign;
/*
 * 更新签到时间
 */
class UpdateUserSignAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,签到时间更新失败！', 'code' => 1001);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 第一步:更新签到时间
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateUserSign($this->params)) {
            $this->setSuccessor(new CreateCreditLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /** 更新签到时间 有则更新　无则创建
     * @param $params
     * @return bool
     */
    private function updateUserSign($params)
    {
        return UserSign::updateOrCreate(
            ['user_id' => $params['user_id']],
            ['sign_at' => date('Y-m-d', time()), 'sign_ip' => $params['sign_ip']]
        );
    }
}
