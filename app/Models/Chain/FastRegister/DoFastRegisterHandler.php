<?php

namespace App\Models\Chain\FastRegister;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\FastRegister\CheckCodeAction;
use DB;
use App\Helpers\Logger\SLogger;
/**
 * 快捷注册
 */
class DoFastRegisterHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 第一步:检查验证码和sign是否正确
     * 第二步:用户主表插入数据
     * 第三步:创建用户认证信息
     * 第四步:创建用户身份信息
     * 第五步:生成用户token值
     * 第六步:检查密码是否存在 生成随机密码
     * 第七步：返回用户信息
     *
     */

    /**
     *
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '出错啦', 'code' => 1000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new CheckCodeAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                SLogger::getStream()->error('用户注册, 事务异常-try');
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
	
	            SLogger::getStream()->error('用户注册, 事务异常-catch');
	            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
