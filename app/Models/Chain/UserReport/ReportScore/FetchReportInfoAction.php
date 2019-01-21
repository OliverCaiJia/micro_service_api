<?php

namespace App\Models\Chain\UserReport\ReportScore;

use App\Constants\UserReportConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserReportFactory;
use App\Models\Chain\UserReport\ReportScore\UpdateReportScoreAction;

/**
 * 2. 修改报告任务表中结束时间 一个月有效
 * Class UpdateReportTaskTimeAction
 * @package App\Models\Chain\UserReport\CreditIndustry
 */
class FetchReportInfoAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '报告任务修改结束时间失败！', 'code' => 10002);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function handleRequest()
    {
        if ($this->fetchReportInfo($this->params) == true) {
            $this->setSuccessor(new UpdateReportScoreAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    private function fetchReportInfo($params = [])
    {
        //报告任务id
        $params['reportTaskId'] = $params['report_task_id'];
        //详情
        $info = UserReportFactory::fetchReportinfoById($params);
        //SLogger::getStream()->info('得积分详情', ['data' => $info]);
        $this->params['id'] = $info['id'];

        //根据分数规则计算
        //打分区间
        $minRange = UserReportConstant::REPORT_MIN_RANGE;
        $maxRange = UserReportConstant::REPORT_MAX_RANGE;
        //区间差
        $diffRange = bcsub($maxRange, $minRange, 2);

        //得分区间
        $minScore = UserReportConstant::REPORT_MIN_RANGE_SCORE;
        $maxScore = UserReportConstant::REPORT_MAX_RANGE_SCORE;
        //得分区间差
        $diffScore = bcsub($maxScore, $minScore, 2);
        //粒度
        $grain = bcdiv($diffRange, $diffScore, 2);

        $this->params['maxRange'] = $maxRange;
        $this->params['minRange'] = $minRange;
        $this->params['info'] = $info;
        $this->params['grain'] = $grain;

        return $info;
    }

}
