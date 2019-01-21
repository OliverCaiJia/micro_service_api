<?php

namespace App\Models\Chain\AddIntegral;

use App\Models\Chain\AbstractHandler;
use App\Helpers\Logger\SLogger;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\AddIntegral\CheckCreditStatusAction;

/**
 * 加积分
 */
class DoAddIntegralHandler extends AbstractHandler
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
     * 1.判断是否符合加积分的条件
     *          不符合：返回
     * 2.加积分流水
     * 3.修改用户总积分
     * 4.修改完成状态表
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '请刷新重试，或请联系客服', 'code' => 1000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckCreditStatusAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('加积分, 事务异常-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();

            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('加积分, 事务异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return $result;
    }

}
