<?php

namespace App\Models\Chain\Creditcard\ShadowApply;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\Creditcard\ShadowApply\UpdateCreditCardAction;

/**
 *  马甲包信用卡申请
 */
class DoApplyHandler extends AbstractHandler
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
     * 1.更新信用卡数据
     * 2.添加申请记录
     */
    public function handleRequest()
    {
        $result = ['error' => '对不起,信用卡申请统计失败!', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new UpdateCreditCardAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('信用卡申请统计失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('信用卡申请统计失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
