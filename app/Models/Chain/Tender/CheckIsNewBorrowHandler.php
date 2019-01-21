<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\CheckAmountHandler;
use App\Models\Orm\BorrowTender;

/**
 *
 * @author gsj
 */
class checkIsNewBorrowHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '仅限新手用户投资');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checkIsNewBorrow($this->parms['borrowNid']) == true) {
            $this->setSuccessor(new CheckAmountHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //检测新手投资
    private function checkIsNewBorrow($borrowNid)
    {
//       $time = time();
//        $userTender = $this->getUserTender($this->parms['userId']);
//        if($this->parms['is_new'] ==1 && !empty($userTender) && $userTender->addtime + 30*24*3600 < $time){
//                return false;
//        }
        $userTenderCount = $this->getUserTenderCount($this->parms['userId']);
        if($this->parms['is_new'] ==1 && $userTenderCount == 0){
             return true;
        }
        else if($this->parms['is_new'] != 1)
        {
            return true;
        }
        return false;
    }
    
    // 新手标  取用户注册时间
    public function getUserTender($user_id){
            return BorrowTender::where('user_id', '=', $user_id)->orderBy('id','asc')->first();
    }
    
    //获取用户投资次数
    public function getUserTenderCount($user_id){
            return BorrowTender::where('user_id', '=', $user_id)->count();
    }
}
