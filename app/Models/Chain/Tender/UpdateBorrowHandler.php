<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\AddAccountLogHandler;
//use App\Models\Orm\BorrowTender;
//use App\Models\Orm\Linkages;
//use App\Models\Orm\LinkagesType;
//use App\Models\Orm\Account;
//use App\Models\Orm\BorrowStyle;
use App\Models\Orm\Borrow;

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
class UpdateBorrowHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '交易错误，请联系客服');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        return $this->updateBorrow($this->parms);
    }

    //更新borrow表 投资
    public function updateBorrow($parms)
    {
        $data = self::getBorrow($parms['borrowNid']);
        if(empty($data)) {
            return false;
        }
        $data = array(
            'borrow_account_yes' => $data->borrow_account_yes + $parms['bidAmount'],
            'borrow_account_wait' => $data->borrow_account_wait - $parms['bidAmount'],
            'borrow_account_scale' => ($data->borrow_account_yes + $parms['bidAmount']) / $data->account * 100,
            'tender_times' => $data->tender_times + 1,
        );

        $updated = Borrow::where('borrow_nid', '=', $parms['borrowNid'])->where('borrow_account_wait', '>=', $parms['bidAmount'])->update($data);

        if ($updated) {
            //第三步添加用户资金表记录
            $this->setSuccessor(new AddAccountLogHandler($parms));
            return $this->getSuccessor()->handleRequest();
        }
        return false;
    }

}
