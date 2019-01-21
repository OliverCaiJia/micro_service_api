<?php

namespace App\Models\Chain\UserReport\Zhima;

use App\Constants\UserReportConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserReportFactory;
use App\Strategies\PaymentStrategy;
use App\Models\Chain\UserReport\Zhima\UpdateUserReportAction;

/**
 * 2. 修改sd_user_report_task表中芝麻步骤
 * Class CheckZhimaTaskAction
 * @package App\Models\Chain\UserReport\Zhima
 */
class UpdateUserReportTaskAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '修改报告任务步骤失败！', 'code' => 10003);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 修改sd_user_report_task表中芝麻步骤
     */
    public function handleRequest()
    {
        if ($this->updateUserReportTask($this->params) == true) {
            $this->setSuccessor(new UpdateUserReportAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     * 修改sd_user_report_task表中芝麻步骤
     */
    private function updateUserReportTask($params = [])
    {
        //修改sd_user_report_task表中芝麻步骤
        $params['step'] = UserReportConstant::REPORT_STEP_ZHIMA;
        $params['carrierId'] = 0;
        $term = UserReportFactory::fetchReportPeriod();
        $params['end_time'] = date('Y-m-d H:i:s', strtotime('+' . $term . 'day'));
        $params['serialNum'] = PaymentStrategy::generateId(UserReportFactory::fetchReportTaskLastId(), 'REPORT');
        $params['front_serial_num'] = PaymentStrategy::generateFrontId(UserReportFactory::fetchReportTaskLastId());
        $report = UserReportFactory::createOrUpdateReportTask($params);
        //芝麻任务存在 并且已经完成，则可以进行跳转
        $this->params['zhima_sign'] = 0;
        if (!$report) {
            return false;
        }

        return true;

    }

}
