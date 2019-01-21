<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\AddBorrowTenderHandler;
use App\Models\Orm\BorrowTender;
use App\Models\Orm\Linkages;
use App\Models\Orm\LinkagesType;
use App\Models\Orm\Account;
use App\Models\Orm\BorrowStyle;
use App\Models\Factory\BonusTicketFactory;
use App\Models\Factory\SystemFactory;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TenderChain
 *
 * @author gsj
 */
class CheckBonusTicketHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '红包券不可用');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checkBonusTicket($this->parms['borrowNid'], $this->parms['bonusTicketId'], $this->parms['userId'])) {
            $this->setSuccessor(new CheckIncreaseTicketHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //检测红包券是否可用
    public function checkBonusTicket($borrowNid, $bonusTicketId,$userId)
    {
        $time = time();
        //是否使用红包券
        $nid = 'con_bonusticket';
        $systemInfo = SystemFactory::getSystemOneOption($nid);
        if ($systemInfo->value == 1) {
            // 检测标是否设置了使用红包券
            $data = self::getBorrow($borrowNid);
            if ($data->is_bonusticket == 1) {
                //前端传的券id
                if ($bonusTicketId > 0) {
                    $borrowPeriod = $this->getBonusTicketPeriod($data->borrow_period);
                    $isUse = BonusTicketFactory::checkBonusTicket($userId, $borrowPeriod, $bonusTicketId);
                    if ($isUse === false) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    //红包券期限
    public function getBonusTicketPeriod($borrow_period)
    {
        //$periodArr =['0'=>'0','1'=>'1','2'=>'1-3个月','3'=>'3个月及以上','4'=>'6个月及以上'];      
        $borrowPeriod = [0];
        if ($borrow_period == 1) {
            $borrowPeriod = [0, 1, 2];
        } elseif (in_array($borrow_period, [2, 3])) {
            $borrowPeriod = [0, 2, 3];
        } elseif (in_array($borrow_period, [4, 5])) {
            $borrowPeriod = [0, 3];
        } elseif ($borrow_period >= 6) {
            $borrowPeriod = [0, 3, 4];
        }
        return $borrowPeriod;
    }

}
