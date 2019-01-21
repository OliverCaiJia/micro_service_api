<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\AddBorrowTenderHandler;
use App\Models\Orm\BorrowTender;
use App\Models\Orm\Linkages;
use App\Models\Orm\LinkagesType;
use App\Models\Orm\Account;
use App\Models\Orm\BorrowStyle;
use App\Models\Factory\IncreaseTicketFactory;

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
class CheckIncreaseTicketHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '加息券不可用');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checkIncreaseTicket($this->parms['borrowNid'], $this->parms['increaseTicketId'], $this->parms['userId'], $this->parms['bidAmount'])) {
            $this->setSuccessor(new AddBorrowTenderHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //校验标是否活动专享,专享码是否正确
    public function checkIncreaseTicket($borrowNid, $increaseTicketId, $userId, $bidAmount)
    {
        $time = time();
        $data = self::getBorrow($borrowNid);
        // 检测标是否设置了使用加息券
        if ($data->is_allow_increase == 1) {
            if ($increaseTicketId > 0) {
                $increase = IncreaseTicketFactory::checkIncreaseTicket($userId, $bidAmount, $increaseTicketId);
                if (!$increase) {
                    return false;
                }
            }
        }
        return true;
    }

}
