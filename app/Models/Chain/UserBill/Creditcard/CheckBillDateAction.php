<?php

namespace App\Models\Chain\UserBill\Creditcard;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;
use App\Models\Chain\UserBill\Creditcard\CheckAlreadyRepayAction;

/**
 * 1.验证时间是否重复
 * Class CheckBillDateAction
 * @package App\Models\Chain\UserBill\Creditcard
 */
class CheckBillDateAction extends AbstractHandler
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
            $this->setSuccessor(new CheckAlreadyRepayAction($this->params));
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
    private function checkBilldate($params = [])
    {
        //接收时间的年月
        $year = substr($params['billTime'], 0, 4);
        $month = substr($params['billTime'], -2, 2);
        $billTime = $year . '-' . $month;
        $this->params['bill_time'] = $billTime;

        //获取账单id
        $billIds = UserBillPlatformFactory::fetchBillIdsById($params['creditcardId']);
        //不包含修改月份账单id
        $selfId = [$params['billId']];
        $billIds = array_diff($billIds, $selfId);
        //该用户所有账单对应账单时间
        $billTimes = UserBillFactory::fetchBankBillTimes($billIds);

        //相同的账户下不能出现相同的月份
        if (in_array($billTime, $billTimes)) {
            return false;
        }

        return true;
    }
}
