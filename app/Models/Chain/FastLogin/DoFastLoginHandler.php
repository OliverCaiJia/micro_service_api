<?php

namespace App\Models\Chain\FastLogin;

use App\Models\Chain\AbstractHandler;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\FastLogin\CheckUserLockAction;
use DB;

class DoFastLoginHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 验证码快捷登录
     *
     * 第一步: 检查用户是否被锁定
     * 第二步: 检查验证码code和sign是否正确
     * 第三步: 更新用户最后登录时间
     * 第四步: 刷新token
     * 第五步: 检查是否设置密码 随机生成密码
     * 第六步: 检查是否设置身份 默认工薪族身份
     * 第七步: 将用户信息返回
     *
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦', 'code' => 1000];

        DB::beginTransaction();
        try {
            $this->setSuccessor(new CheckUserLockAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error'])) {
                DB::rollback();

                SLogger::getStream()->error('快捷注册, 事务异常-try');
                SLogger::getStream()->error($result['error']);
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();

            SLogger::getStream()->error('快捷注册, 事务异常-catch');
            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
