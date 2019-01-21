<?php

namespace App\Models\Chain\Creditcard\Bill;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CreditcardAccountFactory;

/**
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 * 3.创建或修改sd_bank_creditcard_alert表
 */
class CreateOrUpdateBillAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,修改账单失败！', 'code' => 1003);
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
        if ($this->createOrUpdateBill($this->params) == true) {
            return $this->params;
        } else {
            return $this->error;
        }
    }


    /**
     * @param $params
     * @return bool
     */
    private function createOrUpdateBill($params)
    {
        $re = CreditcardAccountFactory::createOrUpdateBill($params);

        //获取新添加成功的账单id
        $billId = CreditcardAccountFactory::fetchBillId($params);
        $this->params['bill_id'] = $billId['id'];


        return $re;
    }
}
