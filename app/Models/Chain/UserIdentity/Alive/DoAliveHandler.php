<?php

namespace App\Models\Chain\UserIdentity\Alive;

use App\Models\Chain\AbstractHandler;
use Illuminate\Support\Facades\DB;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\UserIdentity\Alive\UploadMegliveBestAction;

/**
 *  活体认证
 */
class DoAliveHandler extends AbstractHandler
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
     * 1.上传最佳照片与全景照片
     * 2.活体认证流水记录
     * 3.活体认证记录，未认证情况
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
		    $this->setSuccessor(new UploadMegliveBestAction($this->params));
		    $result = $this->getSuccessor()->handleRequest();
		    if (isset($result['error'])) {
			    DB::rollback();
			
			    SLogger::getStream()->error('活体认证记录-try');
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
		
		    SLogger::getStream()->error('活体认证记录-catch');
		    SLogger::getStream()->error($e->getMessage());
	    }
	    return $result;
    }

}
