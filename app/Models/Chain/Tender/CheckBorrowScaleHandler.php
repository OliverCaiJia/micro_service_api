<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\CheckUserHandler;
//use App\Models\Orm\BorrowTender;
//use App\Models\Orm\Linkages;
//use App\Models\Orm\LinkagesType;
//use App\Models\Orm\Account;
//use App\Models\Orm\BorrowStyle;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TenderChain
 *
 * @author Administrator
 */
class CheckBorrowScaleHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '项目已经投满，请查看其他项目信息');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checkBorrowScale($this->parms['borrowNid']) == true) {
            $this->setSuccessor(new CheckUserHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //查询是否满标
    private function checkBorrowScale($borrowNid)
    {
        $data = $this->getBorrow($borrowNid);
        if (empty($data) || $data->borrow_account_wait <= 0) {
            return false;
        }
        $this->parms['is_new'] = $data->is_new;
        return true;
    }

}
