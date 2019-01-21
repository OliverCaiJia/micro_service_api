<?php

namespace App\Models\Chain\Cash;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Cash\CheckCashDataAction;
use App\Helpers\Logger\SLogger;
use Illuminate\Support\Facades\DB;

/**
 * 现金提现
 */
class DoCashHandler extends AbstractHandler
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
     * 1.判断传值大小
     * 2.用户账户流水表插入数据
     * 3.用户提现流水表插入数据
     * 4.用户账户表减现金值
     *
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '提现出错啦', 'code' => 7000];

        DB::beginTransaction();
        try
        {

            $this->setSuccessor(new CheckCashDataAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                SLogger::getStream()->error('现金提现, 事务异常-try');
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

            SLogger::getStream()->error('现金提现, 事务异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return $result;
    }

}
