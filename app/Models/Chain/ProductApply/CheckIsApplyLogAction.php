<?php
namespace App\Models\Chain\ProductApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserCreditProductLog;
use App\Models\Chain\ProductApply\CreateCreditProductLogAction;

class CheckIsApplyLogAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '产品已经申请,不再加积分!', 'code' => 8002);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return bool
     *
     */
    public function handleRequest()
    {
        if ($this->checkIsApplyLog($this->params) == true) {
            //没有申请
            $this->setSuccessor(new CreateCreditProductLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            //已申请
            return true;
        }
    }

    /**
     * @param $params
     * @return bool
     * 判断产品是否申请
     */
    private function checkIsApplyLog($params)
    {
        $productLogObj = UserCreditProductLog::select('id')
            ->where(['user_id' => $params['userId'], 'config_id' => $params['configId']])
            ->first();
        if (empty($productLogObj)) {
            //没申请
            return true;
        }
        return false;
    }
}