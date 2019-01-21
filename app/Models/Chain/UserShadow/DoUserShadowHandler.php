<?php

namespace App\Models\Chain\UserShadow;

use App\Models\Chain\AbstractHandler;
use App\Helpers\Logger\SLogger;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\UserShadow\CheckShadowNidAction;

/**
 * 马甲
 */
class DoUserShadowHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        set_time_limit(0);
        ignore_user_abort();
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 思路：
     *  1.判断shadow_nid 是否存在于表sd_shadow_count表中
     *      不存在 return false;
     *  2.判断表sd_user_shadow中是否存在唯一的shadow_id与user_id(马甲是否注册)
     *      存在 已注册 返回
     *      否则 继续
     *  3.sd_shadow_log中插入数据
     *  4.注册用户sd_user_shadow插入用户数据
     *  5.更新sd_shadow_count表中注册总量
     *  6.完成马甲注册统计
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '马甲渠道出错，请刷新重试', 'code' => 1000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckShadowNidAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('马甲, 事务异常-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('马甲, 事务异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return $result;
    }

}
