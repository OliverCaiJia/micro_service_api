<?php

namespace App\Models\Chain\Tender;

use \DB;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\BorrowTender;
use App\Models\Orm\Linkages;
use App\Models\Orm\LinkagesType;
use App\Models\Orm\Account;
use App\Models\Orm\BorrowStyle;
use App\Helpers\Utils\Utils;
use App\Models\Factory\InterestFactory;

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
class AddBorrowRecoverHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '9999');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        return $this->addBorrowRcover($this->parms);
    }

    //加入还款计划
    public function addBorrowRcover($data)
    {
        $borrow = $this->getBorrow($data['borrowNid']);
        $recover = InterestFactory::tenderCalculator($data['borrowNid'], $data['bidAmount'], 'all');

        $borrowRecover = array();
        foreach ($recover['list'] as $key => $value) {
            $period = $key + 1;
            $nid = $data['borrowNid'] . '_' . $data['userId'] . '_' . $data['tenderId'] . '_' . $period;
            $borrowRecover[$key]['nid'] = $nid;
            $borrowRecover[$key]['recover_type'] = 'wait';
            $borrowRecover[$key]['addtime'] = time();
            $borrowRecover[$key]['addip'] = Utils::ipAddress();
            $borrowRecover[$key]['user_id'] = $data['userId'];
            $borrowRecover[$key]['borrow_nid'] = $data['borrowNid'];
            $borrowRecover[$key]['status'] = 0;
            $borrowRecover[$key]['borrow_userid'] = $borrow->user_id;
            $borrowRecover[$key]['tender_id'] = $data['tenderId'];
            $borrowRecover[$key]['recover_period'] = $period;
            $borrowRecover[$key]['recover_time'] = $value['repayTime'];
            $borrowRecover[$key]['recover_account'] = $value['monthlyInterest'] + $value['borrowAmount'];
            $borrowRecover[$key]['recover_interest'] = $value['monthlyInterest'];
            $borrowRecover[$key]['recover_capital'] = $value['borrowAmount'];
        }

        DB::table('diyou_borrow_recover')->insert($borrowRecover);
        return array(
            'id' => $data['tenderId'],
            'borrowId' => $data['borrowNid'],
            'actualBidAmount' => $data['bidAmount'],
            'borrowPeriod' => $borrow->borrow_period,
            'interestTotal' => number_format($recover['grossInterest'], 2, '.', '')
        );
    }

}
