<?php

namespace App\Models\Chain\UserBank\LastPay;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\UserBank\LastPay\DeleteLastPayAction;

/**
 * 更换支付银行卡
 * Class DoDeleteHandler
 * @package App\Models\Chain\UserBank\LastPay
 */
class DoLastPayHandler extends AbstractHandler
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
     * 1.取消上次默认支付卡
     * 2.设置本次支付卡
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦', 'code' => 10000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new DeleteLastPayAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('设置支付卡失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('设置支付卡失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
