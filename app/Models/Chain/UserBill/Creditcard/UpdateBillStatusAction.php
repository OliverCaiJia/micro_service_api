<?php

namespace App\Models\Chain\UserBill\Creditcard;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Chain\UserBill\Creditcard\UpdateUserBillAction;

/**
 * 4.有账单日<新添加的账单日的账单，则将状态设为已还
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 *
 */
class UpdateBillStatusAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,账单添加有误！', 'code' => 1003);
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
        if ($this->updateBillStatus($this->params) == true) {
            $this->setSuccessor(new UpdateUserBillAction($this->params));
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
    private function updateBillStatus($params)
    {
        //平台对应账单ids
        $billIds = UserBillFactory::fetchRelBillIdsById($params['creditcardId']);
        //最大还账单时间
        $billinfo = UserBillFactory::fetchMaxBillTimeByBillIds($billIds);

        //新账单状态
        $this->params['new_bill_status'] = 0;

        if (!empty($billinfo) && strtotime($billinfo['bank_bill_time']) < strtotime($params['bill_time'])) {
            //改为已还，新账单未还
            $updateBill['userId'] = $params['userId'];
            $updateBill['billId'] = $billinfo['id'];
            $updateBill['bill_status'] = 1;
            $update = UserBillFactory::updateBillStatusById($updateBill);
            if (!$update) {
                return false;
            }
            $this->params['new_bill_status'] = 0;
        } elseif (!empty($billinfo) && strtotime($billinfo['bank_bill_time']) > strtotime($params['bill_time'])) {
            $this->params['new_bill_status'] = 1;
        }
        return true;
    }


}


