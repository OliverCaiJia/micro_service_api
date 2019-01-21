<?php

namespace App\Models\Chain\Creditcard\Bill;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\Creditcard\Bill\CheckBilldateAction;

/**
 *  还款提醒-账单
 */
class DoBillHandler extends AbstractHandler
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
     * 1.插入流水sd_bank_creditcard_alert_log表
     * 2.验证修改账单状态是否合法
     * 3.创建或修改sd_bank_creditcard_alert表
     */
    public function handleRequest()
    {
        $result = ['error' => '对不起，账单数据有问题！', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckBilldateAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('添加账单失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('添加账单失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
