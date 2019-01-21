<?php

namespace App\Models\Chain\Tender;

use \DB;
use App\Models\Factory\CommonFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\BorrowTender;
use App\Models\Orm\AccountBalance;
use App\Models\Orm\Linkages;
use App\Models\Orm\LinkagesType;
use App\Models\Orm\Account;
use App\Models\Orm\AccountLog;
use App\Models\Orm\BorrowStyle;
use App\Helpers\Utils\Utils;

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
class AddAccountLogHandler extends AbstractHandler
{

    private $parms = array();
    private $error = array('error' => '用户账户添加记录');

    public function __construct($parms)
    {
        $this->parms = $parms;
    }

    public function handleRequest()
    {
        if ($this->putAccountLog($this->parms)) {
            return true;
        }
        return false;
    }

    //添加账户记录
    public function putAccountLog($parms)
    {
        $data = $this->selectAccount($parms['userId']);
        if ($data == false) {
            return false;
        }
        $borrow = $this->getBorrow($parms['borrowNid']);
        $logInfo = $this->constant($borrow, $parms);
        $accountLog = $this->addAccountLog($logInfo);
        if ($accountLog == false) {
            return false;
        }
        if (!$this->addAccount($accountLog, $data)) {
            return false;
        }

        if (!$this->updateAccountLog($accountLog, $parms['userId'])) {
            return false;
        }
        if (!$this->insertAccountBalance($accountLog)) {
            return false;
        }

        return true;
    }

    //数组初始化
    public function constant($borrow, $parms)
    {
        return array(
            'user_id' => $parms['userId'],
            'account_web_status' => 0,
            'account_user_status' => 0,
            'nid' => 'tender_frost_' . $parms['userId'] . '_' . $parms['borrowNid'] . '_' . $parms['tenderId'],
            'borrow_nid' => $parms['borrowNid'],
            'code' => 'borrow',
            'code_type' => 'tender',
            'code_nid' => $parms['tenderId'],
            #投资金额
            'money' => $parms['bidAmount'],
            #收入
            'income' => 0,
            #支出
            'expend' => 0,
            #可提现
            'balance_cash' => 0,
            #当前 投资金额  不可提现冻结金额
            'balance_frost' => -$parms['bidAmount'],
            #冻结金额 当前投资金额
            'frost' => $parms['bidAmount'],
            #待收
            'await' => 0,
            #待还
            'repay' => 0,
            #资金操作类型
            'type' => 'tender',
            #付钱给借款人
            'to_userid' => $borrow->user_id,
            #备注
            'remark' => "投标<a href=/invest/a" . $borrow->borrow_nid . ".html target=_blank>" . $borrow->name . "</a>所冻结资金",
        );
    }

    //account表查询
    public function selectAccount($userId)
    {
        $data = array();
        $data = $this->getAccount($userId);
        if (empty($data)) {
            $account = array(
                'total' => 0,
                'user_id' => $userId,
            );
            $insertId = DB::table('diyou_account')->insertGetId($account);
            if (!$insertId) {
                return false;
            }
        }
        return $data;
    }

    //account_log 新增记录
    public function addAccountLog($logInfo)
    {
        //新不可提现冻结金额
        $logInfo['balance_cash_new'] = $logInfo['balance_cash'];
        $logInfo['balance_frost_new'] = $logInfo['balance_frost'];
        $logInfo['balance_new'] = $logInfo['balance_cash_new'] + $logInfo['balance_frost_new'];
        $logInfo['income_new'] = $logInfo['income'];
        $logInfo['expend_new'] = $logInfo['expend'];
        $logInfo['frost_new'] = $logInfo['frost'];
        $logInfo['await_new'] = $logInfo['await'];
        $logInfo['repay_new'] = $logInfo['repay'];
        $logInfo['addtime'] = Utils::getDBTime();
        $logInfo['addip'] = Utils::ipAddress();
        $insertId = DB::table('diyou_account_log')->insertGetId($logInfo);
        if (!$insertId) {
            return false;
        }
        $logInfo['accountLogId'] = $insertId;
        return $logInfo;
    }

    //account表更新记录 更新用户资金
    public function addAccount($logInfo, $account)
    {
        $data = array();
        $data['income'] = $account->income + $logInfo['income'];
        $data['expend'] = $account->expend + $logInfo['expend'];
        $data['balance_cash'] = $account->balance_cash + $logInfo['balance_cash'];
        $data['balance_frost'] = $account->balance_frost + $logInfo['balance_frost'];
        $data['frost'] = $account->frost + $logInfo['frost'];
        $data['await'] = $account->await + $logInfo['await'];
        $data['balance'] = $account->balance_cash + $data['balance_frost'];
        $data['repay'] = $account->repay + $logInfo['repay'];
        $data['total'] = $account->frost + $account->await + $account->balance;
        $boolean = Account::where('user_id', '=', $account->user_id)->update($data);
        return $boolean;
    }

    //account_log 再次更新表记录。 记录新增account操作
    public function updateAccountLog($logInfo, $userId)
    {
        $account = $this->selectAccount($userId);
        $accountLog = AccountLog::where('id','=',$logInfo['accountLogId'])->first();
        $data = array(
            'income' => $logInfo['income'],
            'expend' => $logInfo['expend'],
            'balance_cash' => $account->balance_cash,
            'balance_cash_old' => $accountLog->balance_cash - $accountLog->balance_cash_new,
            'balance_frost' => $account->balance_frost,
            'balance_frost_old' => $account->balance_frost - $accountLog->balance_frost_new,
            'balance' => $account->balance,
            'balance_old' => $account->balance - $accountLog->balance_new,
            'frost' => $account->frost,
            'frost_old' => $account->frost - $accountLog->frost_new,
            'await' => $account->await,
            'await_old' => $account->await - $accountLog->await_new,
            'repay' => $account->repay,
            'repay_old' => $accountLog->repay - $accountLog->repay_new,
            'total_old' => $account->total,
            'total' => $account->balance + $account->frost + $account->await,
        );
        return AccountLog::where('id', '=', $logInfo['accountLogId'])->update($data);
    }

    //加入网站财务表 account_balance
    public function insertAccountBalance($logInfo)
    {
        $data = AccountBalance::where('nid', '=', $logInfo['nid'])->first();
        if (!empty($data)) {
            return true;
        }
        $accountBalance = AccountBalance::orderBy('id', 'desc')->first();
        if (!$accountBalance) {
            $accountBalance = (object)$accountBalance;
            $accountBalance->total = 0;
            $accountBalance->balance = 0;
            $logInfo['income'] = 0;
        }

        $balance = array(
            'total' => $accountBalance->total + $logInfo['income'] + $logInfo['expend'],
            'balance' => $accountBalance->balance + $logInfo['income'],
            'income' => $logInfo['income'],
            'expend' => $logInfo['expend'],
            'type' => $logInfo['type'],
            'money' => $logInfo['money'],
            'user_id' => $logInfo['user_id'],
            'nid' => $logInfo['nid'],
            'remark' => $logInfo['remark'],
            'addtime' => Utils::getDBTime(),
            'addip' => Utils::ipAddress(),
        );
        $insertId = DB::table('diyou_account_balance')->insertGetId($balance);
        if ($insertId > 0 && !empty($insertId)) {
            return true;
        }
        return false;
    }

}
