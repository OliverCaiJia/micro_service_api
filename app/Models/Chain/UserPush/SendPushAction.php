<?php
namespace App\Models\Chain\UserPush;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\EventFactory;
use App\Services\Core\Push\PushService;

class SendPushAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '填写完信用资料，推送消息', 'code' => 2206);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.填写完信用资料，推送消息
     */
    public function handleRequest()
    {
        if ($this->sendPush($this->params)) {
            return true;
        } else {
            return true;
        }
    }

    /**
     * @param $params
     *  填写完信用资料，推送消息
     */
    private function sendPush($params)
    {
        if($params['notice_id'] != 0) {
            
            //查询推送内容
            $eventMessageArr = EventFactory::fetchEventMessageArray($params['notice_id']);
            $eventHeap = json_decode($eventMessageArr['content'], true);
            //推送
            PushService::sendPush($params,$eventHeap);
            return true;
        }

        return true;

    }


}
