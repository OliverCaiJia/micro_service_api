<?php

namespace App\Models\Chain\UserPush;

use App\Models\Chain\AbstractHandler;
use App\Helpers\Logger\SLogger;
use Illuminate\Support\Facades\DB;

/**
 * 填完信息进行推送
 */
class DoUserPushHandler extends AbstractHandler
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
     * 1.判断信用资料的完善程度
     * 2.100%
     *         加积分
     *         发短息
     *         推送图片
     * <100%  没有变化
     *
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '推送加积分', 'code' => 2200];
        
        DB::beginTransaction();
        try {
            
            $this->setSuccessor(new CheckProgressAction($this->params));
            $result = $this->getSuccessor()->handleRequest();

            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('推送加积分, 事务异常-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('推送加积分, 事务异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        //dd($result);
        return $result;
    }

}
