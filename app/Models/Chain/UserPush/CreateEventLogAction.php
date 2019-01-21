<?php
namespace App\Models\Chain\UserPush;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\EventFactory;
use App\Models\Factory\UserFactory;

class CreateEventLogAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '推送加积分，推送流水表', 'code' => 2204);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.推送日志统计
     */
    public function handleRequest()
    {
        if ($this->createEventLog($this->params) == true) {
            $this->setSuccessor(new SendSmsAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 推送日志统计
     */
    private function createEventLog($params)
    {
        $userInfo = UserFactory::fetchUserNameAndMobile($params['user_id']);
        //在params中添加数据
        $this->params['username'] = $userInfo['username'];
        $this->params['mobile']   = $userInfo['mobile'];

        //推送日志表添加记录
        $eventLog = EventFactory::createEventLog($this->params);
        
        return $eventLog;
    }


}
