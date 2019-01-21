<?php

namespace App\Models\Chain\UserReport\Zhima;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserReportFactory;
use App\Models\Chain\UserReport\Zhima\UpdateUserReportTaskAction;

/**
 * 1. 芝麻信用正在处理中
 * Class CheckZhimaTaskAction
 * @package App\Models\Chain\UserReport\Zhima
 */
class CheckZhimaTaskAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '芝麻信用正在处理中！', 'code' => 10001);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 芝麻信用正在处理中
     */
    public function handleRequest()
    {
        if ($this->checkZhimaTask($this->params) == true) {
            $this->setSuccessor(new UpdateUserReportTaskAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return array|bool
     * 芝麻信用正在处理中
     */
    private function checkZhimaTask($params = [])
    {
        //查询sd_user_zhima_task status=2 更新表sd_user_report_task
        $zhimaStep = UserReportFactory::fetchZhimaTaskById($params);
        if ($zhimaStep != 2) {
            return false;
        }

        return true;
    }

}
