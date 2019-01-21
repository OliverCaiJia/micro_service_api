<?php

namespace App\Models\Chain\UserBank\Defaultcard;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserBank\Defaultcard\DeleteDefaultAction;

/**
 * 设置默认储蓄卡
 * Class DoDefaultcardHandler
 * @package App\Models\Chain\UserBank\Defaultcard
 */
class DoDefaultcardHandler extends AbstractHandler
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
     * 1.取消默认储蓄卡
     * 2.设置最新的默认储蓄卡
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦', 'code' => 10000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new DeleteDefaultAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('默认银行卡失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('默认银行卡失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
