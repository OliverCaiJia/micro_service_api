<?php

namespace App\Models\Chain\UserBill\Creditcard;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserBill\Creditcard\CheckBillDateAction;

/**
 * 添加信用卡账单
 * Class DoCreditcardHandler
 * @package App\Models\Chain\UserBill\Creditcard
 */

class DoCreditcardHandler extends AbstractHandler
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
     * 1.验证本月是否已存在账单
     * 2.已还点击之后不可进行修改
     * 3.存sd_user_bill_log表
     * 4.有账单日<新添加的账单日的账单，则将状态设为已还
     * 5.创建或修改sd_user_bill，并返回相应的bill_id
     * 6.存关系表sd_user_bill_platform_bill_rel
     * 7.返回账单信息
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '添加信用卡账单出错啦', 'code' => 10000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new CheckBilldateAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('添加信用卡账单-try');
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

            SLogger::getStream()->error('添加信用卡账单-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
