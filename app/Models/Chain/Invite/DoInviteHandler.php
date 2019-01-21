<?php

namespace App\Models\Chain\Invite;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;

/**
 *
 */
class DoInviteHandler extends AbstractHandler
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
        $result = ['error' => '出错啦', 'code' => 10001];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new CreateInviteLogAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('注册邀请失败-try');
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

            SLogger::getStream()->error('注册邀请失败-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
