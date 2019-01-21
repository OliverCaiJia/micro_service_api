<?php

namespace App\Models\Chain\Order\VipOrder;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;

/**
 * 会员订单一条责任链
 */
class DoVipOrderLogicHandler extends AbstractHandler
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
     * 0.检查会员状态
     * 1.添加会员
     * 2.创建订单【订单id】
     * 3.设置本次支付的银行卡
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
            $this->setSuccessor(new CheckVipStatusAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                SLogger::getStream()->error('vip订单失败, vip-try');
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

            SLogger::getStream()->error('vip订单捕获异常, vip异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return $result;
    }

}
