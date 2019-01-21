<?php

namespace App\Models\Chain\Creditcard\Bill;

use App\Models\Chain\AbstractHandler;
use App\Models\Orm\BankCreditcardBill;
use App\Models\Chain\Creditcard\Bill\CreateBillLogAction;
use Illuminate\Support\Facades\DB;

/**
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 * 1.验证时间是否重复
 */
class CheckBilldateAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,您已添加过该月份账单！', 'code' => 1000);
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
        if ($this->checkBilldate($this->params) == true) {
            $this->setSuccessor(new CreateBillLogAction($this->params));
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
    private function checkBilldate($params)
    {
        //接收时间的年月
        $year = substr($params['billTime'], 0, 4);
        $month = substr($params['billTime'], -2, 2);
        $billTime = $year . '-' . $month;

        $alert = BankCreditcardBill::select(DB::raw("date_format(bill_time,'%Y-%m') as bill_time "))
            ->where(['user_id' => $params['userId'], 'account_id' => $params['accountId'], 'bill_status' => 1, 'status' => 0])
            ->pluck('bill_time')->toArray();

        //相同的账户下不能出现相同的月份
        if (in_array($billTime, $alert)) {
            return false;
        }

        return true;
    }
}
