<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\CheckPayPasswordHandler;
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
 * @author zhaoqiying
 */
class CheckBlanceHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '可用金额不足', 'code' => 111);

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checkBlance($this->parms['userId'], $this->parms['bidAmount']) == true) {
            $this->setSuccessor(new CheckPayPasswordHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //检查输入金额 是否大于账户可用余额,如果大于可用余额，则该账户可用余额全部用于投资
    //如果用户可用金额小于100 则不投资。否则将账户可用余额一百的整数倍用于投资
    public function checkBlance($userId, $bidAmount)
    {
        $data = self::getAccount($userId);
        //如果可用金额小于100
        if (empty($data) || $data->balance < 100) {
            return false;
        }
        //则该账户可用余额全部用于投资,反之则用投资金额 100的整数倍
        if ($bidAmount > $data->balance) {
            $balance  = $data->balance;
            $bidAmount=  intval($balance / 100) * 100;
            $this->parms['bidAmount'] = intval($bidAmount);
            if($bidAmount <= 0){
                return false;
            }
            if($this->parms['tenderStatus'] == 0){
                $this->error = ['error' => '投资金额大于账号余额'];
                return false;
            }
        }

        return true;
    }

}
