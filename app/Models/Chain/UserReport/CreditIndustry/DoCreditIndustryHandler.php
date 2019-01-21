<?php

namespace App\Models\Chain\UserReport\CreditIndustry;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserReport\CreditIndustry\UpdateQueriedOrgTypeAnalyzeAction;

/**
 *  行业信贷分析数据
 *  根据速贷之家规则生成行业信贷分析数据
 * Class DoCreditIndustryHandler
 * @package App\Models\Chain\UserReport\Zhima
 */
class DoCreditIndustryHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 1. 机构信息分类统计  处理数据  存数据库
     * 2. 修改报告任务表中结束时间 一个月有效
     * 3. 行业信贷分析      处理数据  存数据库
     * 4. 返回结果
     */

    /**
     * @return mixed
     */
    public function handleRequest()
    {
        $result = ['error' => '对不起,速贷之家规则统计信用报告失败!', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new UpdateQueriedOrgTypeAnalyzeAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('速贷之家规则统计信用报告失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('速贷之家规则统计信用报告失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
