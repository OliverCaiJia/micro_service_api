<?php

namespace App\Models\Chain\ProductApply;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\ProductApply\CheckConfigByProductIdAction;

/**
 * 产品申请
 */
class DoProductApplyHandler extends AbstractHandler
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
     * 1.接收产品id 判断是否存在于产品配置表中
     * 1.1 验证是否以前申请过，申请过不加积分
     * 2.存在  积分产品申请流水表插入数据
     * 3.积分流水表加积分
     * 4.用户积分表加积分
     * 5.判断是否被邀请过
     *      已邀请    邀请流水表  先查询后插入   查询有值修改状态（邀请中、已注册、已申请）
     *             6.判断之前是否申请过  第一次申请加积分
     *             第一次申请：
     *                  7.邀请人账户流水表插入数据
     *                  8.邀请人账户表更新数据
     *            别的申请：
     *                  return  true
     *      未邀请  true
     */


    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '积分产品申请出错啦！', 'code' => 8000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new CheckConfigByProductIdAction($this->params));
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
