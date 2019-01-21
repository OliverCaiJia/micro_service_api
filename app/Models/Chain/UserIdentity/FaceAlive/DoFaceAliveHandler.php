<?php

namespace App\Models\Chain\UserIdentity\FaceAlive;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserIdentity\FaceAlive\AliveVerifyAction;

/**
 *  face++活体认证
 */
class DoFaceAliveHandler extends AbstractHandler
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
     *
     * 1.face++验证活体
     * 2.修改活体认证状态
     * 3.修改sd_user_realname表状态
     * 4.同步sd_user_profile表
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦', 'code' => 10000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new AliveVerifyAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('face++活体认证-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('face++活体认证-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
