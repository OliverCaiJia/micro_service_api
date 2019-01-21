<?php

namespace App\Models\Chain\Creditcard\Bill;

use App\Models\Chain\AbstractHandler;
use App\Models\Orm\BankCreditcardBill;
use App\Models\Chain\Creditcard\Bill\CreateOrUpdateBillAction;

/**
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 * 2.验证修改账单状态是否合法
 */
class CheckBillStatusAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,账单修改状态不合法！', 'code' => 1002);
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
        if ($this->checkBillstatus($this->params) == true) {
            $this->setSuccessor(new CreateOrUpdateBillAction($this->params));
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
    private function checkBillstatus($params)
    {
        $alert = BankCreditcardBill::select(['id'])->where(['user_id' => $params['userId'], 'account_id' => $params['accountId'], 'bill_time' => $params['billTime'], 'status' => 0])->first();

        if (!$alert && $params['billStatus'] == 1) {
            //账单不存在可以进行创建
            return false;
        }

        return true;
    }
}
