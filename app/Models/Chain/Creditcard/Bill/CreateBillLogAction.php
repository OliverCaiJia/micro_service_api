<?php

namespace App\Models\Chain\Creditcard\Bill;

use App\Models\Factory\CreditcardAccountFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\BankCreditcardBillLog;
use App\Strategies\CreditcardAccountStrategy;
use App\Models\Chain\Creditcard\Bill\CreateOrUpdateAlertAction;

/**
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 * 1.插入流水sd_bank_creditcard_alert_log表
 */
class CreateBillLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,该账单已经还款！', 'code' => 1001);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     *
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->createBillLog($this->params) == true) {
            $this->setSuccessor(new CheckBillStatusAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function createBillLog($params)
    {
        //已还点击之后不可以进行修改
        $isBill = CreditcardAccountFactory::fetchBillIsToUpdate($params);
        if ($isBill) {
            return false;
        }

        //查询账单日期 如果不存在就获取账户日期进行赋值
        $repayDay = CreditcardAccountFactory::fetchBillTime($params);
        if (!$repayDay) {
            //账单日期不存在
            //还款日 日期
            $params['repay_day'] = CreditcardAccountFactory::fetchRepayday($params['accountId']);
            //转化时间
            $params['billTime'] = CreditcardAccountStrategy::getAlertTime($params);
            $this->params['billTime'] = $params['billTime'];

        } else {
            //账单日期存在   不会在根据账户日期进行修改
            $params['repay_day'] = date('d', strtotime($repayDay['bill_time']));
            $params['billTime'] = CreditcardAccountStrategy::getAlertTime($params);
            $this->params['billTime'] = $params['billTime'];
        }

        return CreditcardAccountFactory::createBankCreditcardBillLog($params);
    }
}
