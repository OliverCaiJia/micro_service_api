<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\CheckBlanceHandler;

class CheckMaxTenderAccountHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '超出此标最大投资金额');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checMaxTenderAccount($this->parms['borrowNid'], $this->parms['bidAmount'], $this->parms['userId']) == true) {
            $this->setSuccessor(new CheckBorrowAccountHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //输入金额是否小于最大投资金额
    public function checMaxTenderAccount($borrowNid, $bidAmount, $userId)
    {
        $data = self::getBorrow($borrowNid);
        $tenderAccountMax = $data->tender_account_max;
        if (empty($data) || $tenderAccountMax <= 0) {
            return true;
        }
        //获取用户投资总额
        $userTenderAccount = self::getUserTenderAccount($borrowNid, $userId);
        if ($tenderAccountMax < $bidAmount) {
            $this->error = ['error' => '此标最大投标金额不能大于' . $tenderAccountMax];
            return false;
        } elseif ($userTenderAccount + $bidAmount > $tenderAccountMax) {
            $tenderAccount = $tenderAccountMax - $userTenderAccount;
            $this->error = ['error' => '您已经投标了' . $userTenderAccount . ',最大投标总金额不能大于' . $tenderAccountMax . '，你最多还能投资' . $tenderAccount];
            return false;
        }
        return true;
    }

}
