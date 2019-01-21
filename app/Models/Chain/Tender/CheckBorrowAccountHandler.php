<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\CheckBlanceHandler;


class CheckBorrowAccountHandler extends AbstractHandler
{
    private $parms = array();
    private $error = array('error' => '投资金额大于项目可投金额');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checkBorrowWait($this->parms['borrowNid'], $this->parms['bidAmount']) == true) {
            $this->setSuccessor(new CheckBlanceHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //输入金额是否大于可投金额
    public function checkBorrowWait($borrowNid, $bidAmount)
    {
        $data = self::getBorrow($borrowNid);
        $accountWait = $data->borrow_account_wait;
        if (empty($data) || $accountWait <= 0 ) {
            return false;
        }
        //如果输入金额大于项目可投金额，那么实际扣款金额为项目可投金额
        if($accountWait < $bidAmount){
            $this->parms['bidAmount'] = $accountWait;
            if($this->parms['tenderStatus'] == 0){
                $this->error = ['error' => 'tender_account_error'];
                return false;
            }
        }
        return self::checkdBorrower($data->user_id);
    }
    //检测投标人是否是发标人
    public  function checkdBorrower($userId){
        if($this->parms['userId'] == $userId){
                $this->error = ['error' => '不能投自己发的标'];
                return false;
        }
        return true;
    }

}
