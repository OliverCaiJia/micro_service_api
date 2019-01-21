<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\CheckBorrowAccountHandler;
use App\Models\Orm\BorrowTender;
use App\Models\Orm\Linkages;
use App\Models\Orm\LinkagesType;
use App\Models\Orm\Account;
use App\Models\Orm\BorrowStyle;

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
class CheckAmountHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '输入金额必须是100的倍数');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checkAmount($this->parms['bidAmount']) == true) {
            $this->setSuccessor(new CheckMinTenderAccountHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //判断是输入金额是否为100的倍数
    public function checkAmount($amount)
    {
        if (!is_int($amount / 100) || $amount <= 0) {
            return false;
        }
        return true;
    }

}
