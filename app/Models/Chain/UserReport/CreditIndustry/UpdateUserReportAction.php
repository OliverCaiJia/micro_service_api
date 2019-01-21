<?php

namespace App\Models\Chain\UserReport\CreditIndustry;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserReportFactory;

/**
 * 3. 行业信贷分析      处理数据  存数据库
 * Class UpdateCreditIndustryAnalysisAction
 * @package App\Models\Chain\UserReport\CreditIndustry
 */
class UpdateUserReportAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '行业信贷分析失败！', 'code' => 10002);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateCreditIndustryAnalysis($this->params) == true) {
//            $this->setSuccessor(new UpdateReportScoreAction($this->params));
//            return $this->getSuccessor()->handleRequest();
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    private function updateCreditIndustryAnalysis($params = [])
    {
        //SLogger::getStream()->info('保存信用报告抓取信息',['data'=>$params]);
        $update = UserReportFactory::updateCreditIndustryAnalysis($params);

        return $update;
    }

}
