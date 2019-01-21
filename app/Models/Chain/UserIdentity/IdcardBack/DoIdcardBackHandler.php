<?php

namespace App\Models\Chain\UserIdentity\IdcardBack;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\UserIdentity\IdcardBack\UploadIdcardBackAction;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;

/**
 *  调取face++获取身份证反面信息
 */
class DoIdcardBackHandler extends AbstractHandler
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
     * 1.获取app端传的图片，并上传至七牛
     * 2.调用face++获取身份证反面信息
     * 3.记实名认证流水
     * 4.修改实名认证表
     *
     */

    /**
     * @return mixed]
     */
    public function handleRequest()
    {
	    $result = ['error' => '出错啦', 'code' => 10000];
	    
	    DB::beginTransaction();
	    try
	    {
		    $this->setSuccessor(new UploadIdcardBackAction($this->params));
		    $result = $this->getSuccessor()->handleRequest();
		    if (isset($result['error'])) {
			    DB::rollback();
			
			    SLogger::getStream()->error('调取face++获取身份证反面信息-try');
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
		
		    SLogger::getStream()->error('调取face++获取身份证反面信息-catch');
		    SLogger::getStream()->error($e->getMessage());
	    }
	    return $result;
    }

}
