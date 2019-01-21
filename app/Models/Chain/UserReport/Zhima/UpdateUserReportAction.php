<?php

namespace App\Models\Chain\UserReport\Zhima;

use App\Constants\UserReportConstant;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserReportFactory;
use App\Strategies\PaymentStrategy;

/**
 * 3. 同步sd_user_report表中数据
 * Class CheckZhimaTaskAction
 * @package App\Models\Chain\UserReport\Zhima
 */
class UpdateUserReportAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '同步sd_user_report表中数据失败！', 'code' => 10002);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 同步sd_user_report表中数据
     */
    public function handleRequest()
    {
        if ($this->updateUserReportTask($this->params) == true) {
            return $this->params;
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     * 同步sd_user_report表中数据
     */
    private function updateUserReportTask($params = [])
    {
        //芝麻分数
        $params['score'] = UserReportFactory::fetchZhimaScoreById($params);
        //行业关注名单
        $params['details'] = UserReportFactory::fetchZhimaWatchDetailsById($params);
        //获取report_task_id
        $params['step'] = UserReportConstant::REPORT_STEP_ZHIMA;
        $reportTask = UserReportFactory::fetchEfficationReportByIdAndStep($params);
        $params['report_task_id'] = isset($reportTask['id']) ? $reportTask['id'] : 0;
        $params['serial_num'] = isset($reportTask['serial_num']) ? $reportTask['serial_num'] : '';
        $params['front_serial_num'] = isset($reportTask['front_serial_num']) ? $reportTask['front_serial_num'] : '';

        $report = UserReportFactory::createOrUpdateUserReport($params);
        if (!$report) {
            return false;
        }

        $this->params['zhima_sign'] = 1;

        return true;
    }

}
