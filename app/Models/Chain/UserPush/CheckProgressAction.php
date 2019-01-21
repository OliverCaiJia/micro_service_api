<?php

namespace App\Models\Chain\UserPush;

use App\Constants\CreditConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\EventFactory;
use App\Models\Factory\UserFactory;

class CheckProgressAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '推送加积分', 'code' => 2201);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.判断信用资料的完善程度
     */
    public function handleRequest()
    {
        if ($this->checkProgress($this->params) == true) {
            $this->setSuccessor(new CreateCreditLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 判断信用资料的完善程度
     */
    private function checkProgress($params)
    {
        $progArr = $params['progArr'];
        //查询信息填写总个数
        $indent = UserFactory::fetchUserIndent($params['userId']);
        $userInfoCounts = $progArr['progCounts'];

        $eventLogNum = EventFactory::fetchEventLogNum($params['userId']);
        $eventNum = EventFactory::fetchEventNum();

        //查询积分流水表
        $creditLog = CreditFactory::fetchCreditLogData($params['userId'], CreditConstant::USERINFO_COMPLETE_TYPE);

        if ($creditLog || $eventLogNum || $eventLogNum >= $eventNum) {
            return false;
        }

        if ($indent == 1 && $userInfoCounts == 21) {
            return true;
        } elseif ($indent == 2 && $userInfoCounts == 25) {
            return true;
        } elseif ($indent == 3 && $userInfoCounts == 25) {
            return true;
        } elseif ($indent == 4 && $userInfoCounts == 19) {
            return true;
        } else {
            return false;
        }
    }


}
