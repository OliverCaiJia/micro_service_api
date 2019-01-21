<?php

namespace App\Models\Chain\UserReport\Carrier;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserSign\UpdateUserSignAction;

/**
 *  轮循请求运营商处理状态
 */
class DoCarrierHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 1. 请先进行芝麻认证
     * 2. 修改运营商task
     * 3. 创建或修改 sd_user_report_task
     */

    /**
     * @return mixed
     */
    public function handleRequest()
    {
        $result = ['error' => '对不起,运营商状态返回失败!', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckZhimaStatusAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('运营商状态返回失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('运营商状态返回失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
