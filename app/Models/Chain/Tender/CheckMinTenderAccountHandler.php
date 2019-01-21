<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\CheckBlanceHandler;

class CheckMinTenderAccountHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '投资金额小于此标最小的投资金额');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checMinTenderAccount($this->parms['borrowNid'], $this->parms['bidAmount']) == true) {
            $this->setSuccessor(new CheckMaxTenderAccountHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //输入金额是否大于最小投资金额
    public function checMinTenderAccount($borrowNid, $bidAmount)
    {
        $data = self::getBorrow($borrowNid);
        $tenderAccountMin = $data->tender_account_min;

        if ($tenderAccountMin > $bidAmount) {
            $this->error = ['error' => '最小的投资金额不能小于' . $tenderAccountMin];
            return false;
        }
        return true;
    }

}
