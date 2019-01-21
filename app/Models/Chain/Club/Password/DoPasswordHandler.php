<?php

namespace App\Models\Chain\Club\Password;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Models\Chain\Club\Password\CheckUserClubAction;

/**
 * 论坛修改密码
 */
class DoPasswordHandler extends AbstractHandler
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
     * 1.验证sd_user_club表中是否存在  存在——修改  不存在——不操作
     * 2.调论坛修改密码接口获取返回值
     * 3.论坛中密码修改成功的情况下 修改sd_user_club表中密码
     * 4.返回数据 直接登录
     */

    /**
     * @return mixed]
     * 入口
     */
    public function handleRequest()
    {
        $result = ['error' => '论坛修改密码相关失败', 'code' => 1000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new CheckUserClubAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                SLogger::getStream()->error('论坛修改密码相关失败, 事务异常-try');
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

            SLogger::getStream()->error('论坛修改密码相关失败, 事务异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }

        return $result;
    }

}
