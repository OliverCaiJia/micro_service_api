<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\AddBorrowTenderHandler;
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
class CheckActiveCodeHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '活动专享码不正确');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->checkActiveCode($this->parms['borrowNid'], $this->parms['activeZXCode'])) {
            $this->setSuccessor(new CheckBonusTicketHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }
    
    //校验标是否活动专享,专享码是否正确
    public function checkActiveCode($borrowNid, $activeZXCode)
    {
        $data = self::getBorrow($borrowNid);
        //判断是否活动专享,活动专享是否正确
        if (empty($data) || (strlen($data->activezxcode) > 0 && $activeZXCode != $data->activezxcode)) {
            return false;
        }

        return true;
    }

}
