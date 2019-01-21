<?php

namespace App\Models\Chain\Tender;

use App\Models\Chain\Tender\AddBorrowRecoverHandler;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CommonFactory;
//use App\Models\Orm\BorrowTender;
//use App\Models\Orm\Linkages;
//use App\Models\Orm\LinkagesType;
//use App\Models\Orm\Account;
//use App\Models\Orm\BorrowStyle;
use App\Models\Orm\BorrowCount;
//use App\Models\Orm\BorrowCountLog;
use \DB;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TenderChain
 * 更新投标统计表
 * @author Administrator
 */
class UpdateBorrowCountHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '更新失败');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->addTenderLog($this->parms) == true) {
            $this->setSuccessor(new AddBorrowRecoverHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    //添加投标日志
    public function addTenderLog($data)
    {
        $tenderLog = array(
            'user_id' => $data['userId'],
            'tender_times' => 1,
            'tender_account' => $data['bidAmount'],
            'tender_frost_account' => $data['bidAmount'],
        );
        $remark = serialize($tenderLog);
        $log = array(
            'user_id' => $data['userId'],
            'borrow_nid' => $data['borrowNid'],
            'nid' => 'tender_frost_' . $data['userId'] . '_' . $data['borrowNid'] . '_' . $data['tenderId'],
            'remark' => $remark,
            'addtime' => time(),
        );
        $insertId = DB::table('diyou_borrow_count_log')->insertGetId($log);
        if ($insertId > 0) {
            return $this->updateBorrowCount($tenderLog);
        }
        return false;
    }

    //更新投资统计表
    public function updateBorrowCount($data)
    {
        $borrowCount = CommonFactory::processArray(BorrowCount::where('user_id', '=', $data['user_id'])->first());
        if (empty($borrowCount)) {
            BorrowCount::create(array('user_id' => $data['user_id']));
            $borrowCount['tender_times'] = 0;
            $borrowCount['tender_account'] = 0;
            $borrowCount['tender_frost_account'] = 0;
        }
        $update = array(
            'tender_times' => $borrowCount['tender_times'] + $data['tender_times'],
            'tender_account' => $borrowCount['tender_account'] + $data['tender_account'],
            'tender_frost_account' => $borrowCount['tender_frost_account'] + $data['tender_frost_account'],
        );
        return BorrowCount::where("user_id", '=', $data['user_id'])->update($update);
    }

}
