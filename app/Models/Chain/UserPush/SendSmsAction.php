<?php
namespace App\Models\Chain\UserPush;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\EventFactory;
use App\Models\Factory\SmsFactory;
use App\Models\Factory\UserFactory;

class SendSmsAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '填写完信用资料，发送短信', 'code' => 2205);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.填写完信用资料，发送短信
     */
    public function handleRequest()
    {
        if ($this->sendSms($this->params)) {
            $this->setSuccessor(new SendPushAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return true;
        }
    }

    /**
     * @param $params
     *  填写完信用资料，发送短信
     */
    private function sendSms($params)
    {
        if($params['sms_id'] != 0) {

            //查询发送短信内容
            $sendSmsArr = EventFactory::fetchEventMessageArray($params['sms_id']);
            $smsData = json_decode($sendSmsArr['content'], true);

            $smsData['mobile'] = $params['mobile'];
            
            SmsFactory::SendSmsFromUserinfoComplete($smsData);

            return true;
        }

        return true;
    }


}
