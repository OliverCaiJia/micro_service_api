<?php

namespace App\Models\Chain\UserReport\ReportScore;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserReport\ReportScore\FetchReportInfoAction;

/**
 *  信用报告得分
 *  根据速贷之家产品计算规则进行计算
 * Class DoCreditIndustryHandler
 * @package App\Models\Chain\UserReport\Zhima
 */
class DoReportScoreHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 1.查询信用报告数据，分数基础值
     * 2.计算得分
     */

    /**
     * @return mixed
     */
    public function handleRequest()
    {
        $result = ['error' => '对不起,信用报告得分计算失败!', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new FetchReportInfoAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('信用报告得分计算失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('信用报告得分计算失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
