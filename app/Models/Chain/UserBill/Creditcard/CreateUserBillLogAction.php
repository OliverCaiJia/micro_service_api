<?php

namespace App\Models\Chain\UserBill\Creditcard;

use App\Constants\UserBillPlatformConstant;
use App\Helpers\DateUtils;
use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;
use App\Models\Chain\UserBill\Creditcard\UpdateBillStatusAction;

/**
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 *
 */
class CreateUserBillLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,账单流水添加失败！', 'code' => 1002);
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
            $this->setSuccessor(new UpdateBillStatusAction($this->params));
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
        $params['bill_platform_id'] = $params['creditcardId'];
        $this->params['bill_platform_id'] = $params['creditcardId'];

        //查询该账单的账单日&还款日
        $creditcard = UserBillPlatformFactory::fetchCreditcardInfoById($params);
        if (!$creditcard) {
            //此信用卡不可以添加账单
            $this->error = array('error' => RestUtils::getErrorMessage(2303), 'code' => 2303);
            return false;
        }

        //账单周期  本账单日的下一天开始计算 —— 下一个账单日结束
        $end_bill_date = $params['bill_time'] . '-' . bcadd($creditcard['bank_bill_date'], 0);
        $start_bill_date = date('Y-m-d', strtotime("$end_bill_date -1 month +1 day"));
        $bill_cycle = DateUtils::formatDateToLeftdata($start_bill_date) . '-' . DateUtils::formatDateToLeftdata($end_bill_date);
        $params['bill_cycle'] = $bill_cycle;
        $this->params['bill_cycle'] = $bill_cycle;

        //账单日
        $params['bill_time'] = $params['bill_time'] . '-' . $creditcard['bank_bill_date'];
        $this->params['bill_time'] = $params['bill_time'];

        //还款日
        // 1.账单日<还款日&&账单日还款日相差17天以上，还款日期在同一个月；
        // 2.不满17天 还款日期在下个月
        $bill_time = $params['bill_time'];
        $bank_bill_date = intval($creditcard['bank_bill_date']);
        $bank_repay_day = intval($creditcard['bank_repay_day']);
        //还款日与账单日求差
        $differ = bcsub($bank_repay_day, $bank_bill_date);
        //1.账单日<还款日&&账单日还款日相差17天以上，还款日期在同一个月；
        if ($bank_repay_day > $bank_bill_date && $differ >= UserBillPlatformConstant::BILL_DIFFER_VALUE) {
            $params['repay_time'] = date('Y-m', strtotime($bill_time)) . '-' . $creditcard['bank_repay_day'];
        } else {
            //2.不满17天 还款日期在下个月
            $bill_year_month = date('Y-m', strtotime($bill_time));
            $params['repay_time'] = date('Y-m', strtotime("$bill_year_month +1 month")) . '-' . $creditcard['bank_repay_day'];
        }
        $this->params['repay_time'] = $params['repay_time'];

        //账单金额
        $params['bill_money'] = $params['billMoney'];
        $this->params['bill_money'] = $params['billMoney'];

        //账单状态
        $params['bill_status'] = 0;
        $this->params['bill_status'] = 0;

        //记流水
        $log = UserBillFactory::createUserBillLog($params);
        if (!$log) {
            return false;
        }

        return true;
    }


}


