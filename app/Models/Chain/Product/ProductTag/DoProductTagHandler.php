<?php

namespace App\Models\Chain\Product\ProductTag;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\Product\ProductTag\CreateBlackTagLogAction;

/**
 * 不想看产品标签
 */
class DoProductTagHandler extends AbstractHandler
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
     * 1.接收不想看标签，切割分组
     * 2.如果标签存在，遍历插入流水
     *      如果标签不存在，直接插入流水
     * 3.修改sd_user_product_black_tag，物理删除该用户，该产品下的所有标签
     * 4.如果标签存在，遍历修改
     *      如果标签不存在，直接插入数据
     * 5.验证是否加入过黑名单
     * 6.加入黑名单
     *
     *
     */


    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '添加不想看产品标签出错！', 'code' => 10000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CreateBlackTagLogAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('添加不想看产品标签, 事务异常-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();

            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('添加不想看产品标签, 事务异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return $result;

    }

}
