<?php

namespace App\Models\Chain\Payment\ReportOrder;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Orm\UserReportTask;

class CreateReportTaskAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '创建报告任务失败！', 'code' => 1003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第三步:创建报告task
     * @return array
     */
    public function handleRequest()
    {
        if ($this->createReportTask($this->params)) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     * 创建报告任务
     */
    private function createReportTask($params = [])
    {
        if($params['status'] != 1)
        {
            return true;
        }
        //创建报告任务
        $userId = PaymentFactory::getUserOrderUid($params['orderid']);
        $res = UserReportFactory::createReportTask($userId);
        SLogger::getStream()->info('付费创建报告任务', ['orderid' => $params,'code' => 10200321]);

        return $res;
    }

}
