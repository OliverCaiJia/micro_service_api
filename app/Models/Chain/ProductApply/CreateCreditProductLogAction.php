<?php
namespace App\Models\Chain\ProductApply;

use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserCreditProductLog;
use App\Models\Chain\ProductApply\UpdateCreidtLogAction;

class CreateCreditProductLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '积分产品申请流水表插入数据失败!', 'code' => 8003);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 2.存在  积分产品申请流水表插入数据
     */
    public function handleRequest()
    {
        if ($this->createCreditProductLog($this->params) == true) {
            $this->setSuccessor(new UpdateCreidtLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * 积分产品申请流水表插入数据
     */
    private function createCreditProductLog($params)
    {
        $logObj = new UserCreditProductLog();

        $logObj->user_id    = $params['userId'];
        $logObj->config_id  = $params['configId'];
        $logObj->created_at = date('Y-m-d H:i:s', time());
        $logObj->created_ip = Utils::ipAddress();
        return $logObj->save();

    }

}


