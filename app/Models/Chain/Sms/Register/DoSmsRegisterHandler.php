<?php
namespace App\Models\Chain\Sms\Register;

use App\Models\Chain\AbstractHandler;
use App\Helpers\Logger\SLogger;
use Illuminate\Support\Facades\DB;

/**
 * 短信注册
 */
class DoSmsRegisterHandler extends AbstractHandler
{
    #外部传参

    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * 第一步:根据手机号检测用户是否存在
     * 第二步:用户不存在插入数据  用户存在不插入数据
     * 第三步:发送注册短信
     * 第四步:存短信信息进cache
     *
     */

    /**
     *
     * @return mixed]
     */
    public function handleRequest()
    {
        $result = ['error' => '用户发短信注册出错啦', 'code' => 1000];

        DB::beginTransaction();
        try
        {
            $this->setSuccessor(new CheckUserAction($this->params));
            $result = $this->getSuccessor()->handleRequest();
            if (isset($result['error']))
            {
                DB::rollback();

                SLogger::getStream()->error('用户发短信注册, 事务异常-try');
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
	
	            SLogger::getStream()->error('用户发短信注册, 事务异常-catch');
	            SLogger::getStream()->error($e->getMessage());
        }
        return $result;
    }

}
