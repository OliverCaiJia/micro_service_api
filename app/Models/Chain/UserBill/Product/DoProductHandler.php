<?php

namespace App\Models\Chain\UserBill\Product;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserBill\Creditcard\CheckBillDateAction;

/**
 * 添加或修改网贷
 * Class DoCreditcardHandler
 * @package App\Models\Chain\UserBill\Creditcard
 */
class DoProductHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 平台与账单一起建立思路：
     *
     * 1.修改时验证：超过最后还款月份不可进行修改
     * 2.建立平台流水表
     * 3.验证期数与当前期数是否与数据库中现存数据一致  不一致：重置
     *                                             一致：修改其他数据
     * 4.修改或建立平台表
     * 5.循环建立账单流水表
     * 6.循环建立账单表
     * 7.存关系表sd_user_bill_platform_bill_rel
     * 8.返回相关数据
     *
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '添加或修改网贷平台&账单出错啦', 'code' => 10000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckBillRepayTimeAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('添加或修改网贷平台&账单-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('添加或修改网贷平台&账单-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
