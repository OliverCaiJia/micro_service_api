<?php

namespace App\Models\Chain\Club\Register;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\Club\Register\ClubRegisterAction;

/**
 * 论坛注册
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
     * 0.调论坛注册接口获取返回值
     * 1.将返回值添加到sd_user_club中
     * 2.返回数据 直接登录
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '论坛注册失败', 'code' => 1000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new ClubRegisterAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                SLogger::getStream()->error('论坛注册, 事务异常-try');
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
