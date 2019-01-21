<?php

namespace App\Models\Chain\Credit;

use App\Models\Chain\AbstractHandler;
use App\Helpers\Logger\SLogger;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\Credit\CheckCreditsDataAction;

/**
 * 积分提现
 */
class DoCreditHandler extends AbstractHandler
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
     * 0.判断传值大小
     * 1.积分兑换流水插入数据
     * 2.用户总积分减少
     * 3.用户账户流水插入数据
     * 4.账户增加现金
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '兑换失败，可能积分不足，或请联系客服', 'code' => 6000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new CheckCreditsDataAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                SLogger::getStream()->error('积分兑换, 事务异常-try');
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

            SLogger::getStream()->error('积分兑换, 事务异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return $result;
    }

}
