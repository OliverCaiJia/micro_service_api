<?php

namespace App\Models\Chain\UserBank\Delete;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserBank\Delete\CheckCardAction;

/**
 *  删除银行卡，若删除默认银行卡则设置最近一张银行卡为默认银行卡
 */
class DoDeleteHandler extends AbstractHandler
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
     * 1.只剩最后一张储蓄卡，不允许删除
     * 2.删除银行卡
     * 3.若删除默认银行卡，则默认下一张为默认卡
     * 4.成功
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦', 'code' => 10000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckCardAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('删除银行卡失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('删除银行卡失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
