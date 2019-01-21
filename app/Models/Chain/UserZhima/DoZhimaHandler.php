<?php

namespace App\Models\Chain\UserZhima;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;

class DoZhimaHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed
     * 1.插入芝麻信用流水表
     * 2.更新芝麻信用
     * 3.插入白名单数据
     * 4.更新芝麻任务当前任务:处理完毕
     */
    public function handleRequest()
    {
        $result = ['error' => '插入数据库失败', 'code' => 10001];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CreateUserZhimaLogAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('插入数据库失败-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('插入数据库失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
