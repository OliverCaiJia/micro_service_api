<?php

namespace App\Models\Chain\Order\ReportOrder;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;

/**
 * 报告订单一条责任链
 */
class DoReportOrderLogicHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 思路：
     * 1.创建订单【订单id】
     * 2.设置本次支付的银行卡
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '同步失败', 'code' => 1000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new CreateReportOrderAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                SLogger::getStream()->error('报告订单失败, 报告-try');
                SLogger::getStream()->error($result['error']);
            }
            else
            {
                DB::commit();

            }
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            SLogger::getStream()->error('报告订单捕获异常, 报告异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return $result;
    }

}
