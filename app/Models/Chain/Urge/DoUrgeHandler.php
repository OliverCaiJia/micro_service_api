<?php

namespace App\Models\Chain\Urge;

use App\Models\Chain\AbstractHandler;
use App\Helpers\Logger\SLogger;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\Urge\CheckCreditsDataAction;

/**
 * 催审减积分
 */
class DoUrgeHandler extends AbstractHandler
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
     * 2.修改催审状态
     * 3.向积分流水插入数据
     * 4.用户总积分减少
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '抱歉，积分不足，无法提交催审~', 'code' => 6000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckCreditsDataAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('积分兑换, 事务异常-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();

            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('积分兑换, 事务异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return $result;
    }

}
