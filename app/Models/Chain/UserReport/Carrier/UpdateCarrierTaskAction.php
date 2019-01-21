<?php

namespace App\Models\Chain\UserReport\Carrier;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\UserIdentity\IdcardFront\CreateUserRealnamLogAction;
use App\Models\Factory\UserReportFactory;

/**
 * Class CheckZhimaStatusAction
 * @package App\Models\Chain\UserReport\Carrier
 * 2. 修改运营商task
 */
class UpdateCarrierTaskAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '运营商采集中……！', 'code' => 10002);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 修改运营商task
     */
    public function handleRequest()
    {
        if ($this->createOrUpdateCarrierTask($this->params) == true) {
            $this->setSuccessor(new UpdateReportTaskAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     * 修改运营商task
     */
    private function createOrUpdateCarrierTask($params = [])
    {
        //修改运营商task
        return UserReportFactory::createOrUpdateCarrierTask($params);
    }

}
