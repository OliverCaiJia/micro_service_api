<?php

namespace App\Models\Chain\UserReport\Carrier;

use App\Constants\UserReportConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\UserIdentity\IdcardFront\CreateUserRealnamLogAction;
use App\Models\Factory\UserReportFactory;

/**
 * Class CheckZhimaStatusAction
 * @package App\Models\Chain\UserReport\Carrier
 * 1. 请先进行芝麻认证
 */
class CheckZhimaStatusAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '请先进行芝麻认证！', 'code' => 10001);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 请先进行芝麻认证
     */
    public function handleRequest()
    {
        if ($this->checkZhimaStatus($this->params) == true) {
            $this->setSuccessor(new UpdateCarrierTaskAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     * 请先进行芝麻认证
     */
    private function checkZhimaStatus($params = [])
    {
        //芝麻步骤
        $params['step'] = UserReportConstant::REPORT_STEP_ZHIMA;
        return UserReportFactory::fetchEfficationReportByIdAndStep($params);
    }

}
