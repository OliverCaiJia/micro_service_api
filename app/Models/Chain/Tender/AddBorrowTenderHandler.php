<?php

namespace App\Models\Chain\Tender;

use \DB;
use App\Helpers\Utils\Utils;
use App\Models\Orm\BorrowTender;
use App\Models\Orm\Linkages;
use App\Models\Orm\LinkagesType;
use App\Models\Orm\Account;
use App\Models\Orm\BorrowStyle;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Tender\UpdateBorrowHandler;
use App\Models\Chain\Tender\UpdateBorrowCountHandler;
use App\Models\Chain\Tender\AddBorrowTenderTypeHandler;

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
class AddBorrowTenderHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '投资错误，请联系客服!');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->putBorrowTenderManager($this->parms) == true) {
            $this->setSuccessor(new UpdateBorrowCountHandler($this->parms));
            return $this->getSuccessor()->handleRequest();
        }
        return $this->error;
    }

    //开始投资相关表   一些列操作必须开启事务处理
    public function putBorrowTenderManager($parms)
    {
        //首先开启事务
        DB::beginTransaction();
        //投标第一步 添加投资记录
        $result = self::doInsertBorrowTender($parms);
        if ($result) {
            DB::commit();
            //添加、来源统计
            $this->setSuccessor(new AddBorrowTenderTypeHandler($this->parms['tenderId'],$this->parms['from']));
            $this->getSuccessor()->handleRequest();
            return true;
        }
        DB::rollback();
        return false;
    }

    //投资表新增 borrow_tender  投标第一步
    public function doInsertBorrowTender($parms)
    {
        $time = time();
        $borrowTender = array(
            'addtime' => $time,
            'addip' => Utils::ipAddress(),
            'user_id' => $parms['userId'],
            'account' => $parms['bidAmount'],
            'account_tender' => $parms['bidAmount'],
            'borrow_nid' => $parms['borrowNid'],
            //没有自动投标  所以全部以手动投标状态
            'auto_status' => 0,
            //没有复审通过,状态必须为0
            'status' => 0,
            'nid' => 'tender_' . $parms['borrowNid'] . '_' . $parms['userId'] . '_' .$time,
        );

        //$tender = BorrowTender::create($borrowTender); //这样写不能返回新增ID
        $tenderId = DB::table('diyou_borrow_tender')->insertGetId($borrowTender);
        if ($tenderId) {
            //投标第二步 ，更新借款表
            $parms['tenderId'] = $tenderId;
            $this->parms['tenderId'] = $tenderId;
            $this->setSuccessor(new UpdateBorrowHandler($parms));
            return $this->getSuccessor()->handleRequest();
        }
        return false;
    }

}
