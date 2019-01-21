<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\CheckAmountHandler;
use App\Models\Chain\Tender\checkIsNewBorrowHandler;
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
class CheckUserHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '交易密码尚未设置');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checkUserPaypassword($this->parms['userId']) == true) {
            $this->setSuccessor(new checkIsNewBorrowHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //查询是否设置交易密码
    public function checkUserPaypassword($userId)
    {
        $data = self::getUser($userId);
        if (empty($data) || empty($data->paypassword)) {
            return false;
        }
        return true;
    }

}
