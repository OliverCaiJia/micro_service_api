<?php

namespace App\Models\Chain\UserReport\Zhima;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserReport\Zhima\UpdateUserReportTaskAction;

/**
 *  轮循请求芝麻处理状态 & 同步sd_user_report数据
 */
class DoZhimaHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 1. 芝麻信用正在处理中
     * 2. 修改sd_user_report_task表中芝麻步骤
     * 3. 同步sd_user_report 表中数据
     * 4. 返回状态值
     */

    /**
     * @return mixed
     */
    public function handleRequest()
    {
        $result = ['error' => '对不起,芝麻数据处理返回失败!', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new UpdateUserReportTaskAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('芝麻数据处理返回失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('芝麻数据处理返回失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
