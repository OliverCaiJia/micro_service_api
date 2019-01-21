<?php
namespace App\Models\Chain\UserPush;

use App\Constants\CreditConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\EventFactory;

class CreateCreditLogAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '推送加积分，积分流水表', 'code' => 2202);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.积分流水表
     */
    public function handleRequest()
    {
        if ($this->createCreditLog($this->params) == true) {
            $this->setSuccessor(new UpdateCreditAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 判断信用资料的完善程度
     */
    private function createCreditLog($params)
    {
        //查询推送事件
        $eventArr = EventFactory::fetchEvent();
        //查询信用资料填写完整的推送事件内容
        if ($eventArr['score_id'] != 0) {
            $eventMessageArr = EventFactory::fetchEventMessageArray($eventArr['score_id']);
        } else {
            $eventMessageArr['content'] = '';
            return false;
        }

        //推送加积分数
        $scoreData               = json_decode($eventMessageArr['content'], true);
        $creditLogArr['user_id'] = $params['userId'];
        $creditLogArr['type']    = CreditConstant::USERINFO_COMPLETE_TYPE;
        $creditLogArr['income']  = $scoreData['score'];
        $creditLogArr['remark']  = CreditConstant::USERINFO_COMPLETE_REMARK;

        //添加数据到params
        $this->params['score']     = $scoreData['score'];
        $this->params['user_id']   = $params['userId'];
        $this->params['sms_id']    = $eventArr['sms_id'];
        $this->params['score_id']  = $eventArr['score_id'];
        $this->params['notice_id'] = $eventArr['notice_id'];
        
        $creditLog = CreditFactory::createAddCreditLog($creditLogArr);
        return $creditLog;
    }


}
