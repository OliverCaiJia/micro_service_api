<?php

namespace App\Models\Chain\UserBill\Creditcard;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Chain\UserBill\Creditcard\CreateUserBillRelAction;

/**
 * 4.创建或修改sd_user_bill，并返回相应的bill_id
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 *
 */
class UpdateUserBillAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,账单添加失败！', 'code' => 1004);
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
        if ($this->createOrUpdateUserBill($this->params) == true) {
            $this->setSuccessor(new CreateUserBillRelAction($this->params));
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
    private function createOrUpdateUserBill($params)
    {
        $bill = UserBillFactory::createOrUpdateUserBill($params);
        $this->params['bill_id'] = $bill;

        if (!$bill) {
            return false;
        }

        return true;
    }


}


