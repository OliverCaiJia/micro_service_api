<?php

namespace App\Models\Chain\UserReport\Carrier;

use App\Constants\UserReportConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\UserIdentity\IdcardFront\CreateUserRealnamLogAction;
use App\Models\Factory\UserReportFactory;
use App\Strategies\PaymentStrategy;

/**
 * Class CheckZhimaStatusAction
 * @package App\Models\Chain\UserReport\Carrier
 * 3. 创建或修改 sd_user_report_task
 */
class UpdateReportTaskAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '运营商采集中……！', 'code' => 10003);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 创建或修改 sd_user_report_task
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->createOrUpdateCarrierTask($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     * 创建或修改 sd_user_report_task
     */
    private function createOrUpdateCarrierTask($params = [])
    {
        //查询carrierId
        $params['carrierId'] = UserReportFactory::fetchCarrierIdByTaskId($params);
        //创建或修改 sd_user_report_task
        //运营商通过
        $params['step'] = UserReportConstant::REPORT_STEP_CARRIER;
        $params['serialNum'] = PaymentStrategy::generateId(UserReportFactory::fetchReportTaskLastId(), 'REPORT');
        $params['front_serial_num'] = PaymentStrategy::generateFrontId(UserReportFactory::fetchReportTaskLastId());
        $term = UserReportFactory::fetchReportPeriod();
        $params['end_time'] = date('Y-m-d H:i:s', strtotime('+' . $term . 'day'));
        return UserReportFactory::createOrUpdateReportTask($params);
    }

}
