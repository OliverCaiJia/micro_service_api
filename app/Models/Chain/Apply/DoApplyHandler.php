<?php

namespace App\Models\Chain\Apply;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;

/**
 * 申请借款
 */
class DoApplyHandler extends AbstractHandler
{
    #外部传参
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '立即申请跳转出错啦', 'code' => 10001];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new FetchWebsiteUrlAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('立即申请跳转失败-try');
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

            SLogger::getStream()->error('立即申请跳转失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
