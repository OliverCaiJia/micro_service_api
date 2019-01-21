<?php

namespace App\Models\Chain\UserReport\CreditIndustry;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Strategies\UserReportStrategy;
use App\Models\Chain\UserReport\CreditIndustry\UpdateReportTaskTimeAction;

/**
 * 1. 机构信息分类统计  处理数据  存数据库
 * Class UpdateQueriedOrgTypeAnalyzeAction
 * @package App\Models\Chain\UserReport\Zhima
 */
class UpdateQueriedOrgTypeAnalyzeAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '机构信息分类统计失败！', 'code' => 10002);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     *
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updateQueriedOrgTypeAnalyze($this->params) == true) {
            $this->setSuccessor(new UpdateReportTaskTimeAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * // org_type 类型
     * //type_count 出现次数
     * 'queried_org_type_analyze' => [
     * {
     *      'org_type' => '大范甘迪发',
     *      'type_count' => '1',
     * }
     * ]
     * @param array $params
     * @return bool
     */
    private function updateQueriedOrgTypeAnalyze($params = [])
    {
        $reportinfo = isset($params['reportinfo']) ? $params['reportinfo'] : '';
        //报告id
        $this->params['id'] = isset($reportinfo['id']) ? $reportinfo['id'] : 0;
        //机构查询历史
        $strQueriedInfos = $reportinfo['queried_infos'];
        $queriedInfos = json_decode($strQueriedInfos, true);
        //分类计算org_type出现次数
        $res = empty($queriedInfos) ? [] : array_count_values(array_column($queriedInfos, "org_type"));

        if ($queriedInfos) {
            $loanCnt['now'] = date('Y-m-d', time());
            //查询历史时间
            $loanCnt['dates'] = array_column($queriedInfos, 'date');
            //统计次数
            $data['loan_cnt_15d'] = UserReportStrategy::getLoanCntCount($loanCnt, 15);
            $data['loan_cnt_30d'] = UserReportStrategy::getLoanCntCount($loanCnt, 30);
            $data['loan_cnt_90d'] = UserReportStrategy::getLoanCntCount($loanCnt, 60);
            $data['loan_cnt_180d'] = UserReportStrategy::getLoanCntCount($loanCnt, 180);

            $this->params['queried_infos_analysis'] = json_encode($data);
        }else {
            $this->params['queried_infos_analysis'] = '';
        }
        //SLogger::getStream()->info('机构信息分类统计',['data'=>$this->params]);
        //dd($this->params);
        return true;
    }

}
