<?php

namespace App\Models\Chain\Sns\Register;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\Sns\Register\SnsRegisterAction;

/**
 * SNS注册
 */
class DoRegisterHandler extends AbstractHandler
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
     * 1.注册SNS用户
     * 2.添加sd_user_opensns新数据
     */

    /**
     * 注册责任链
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => 'SNS注册失败', 'code' => 1000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new SnsRegisterAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                SLogger::getStream()->error('SNS注册, 事务异常-try');
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

            SLogger::getStream()->error('论坛注册, 事务异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return $result;
    }

}
