<?php

namespace App\Models\Chain\UserReport\Report;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserSign\UpdateUserSignAction;

/**
 *  生成用户报告
 */
class DoReportHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed
     * 1.签到　更新签到时间
     * 2.添加积分流水
     * 3.用户加积分
     */
    public function handleRequest()
    {
        $result = ['error' => '对不起,生成报告失败!', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new UpdateUserSignAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('用户签到失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('用户签到失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
