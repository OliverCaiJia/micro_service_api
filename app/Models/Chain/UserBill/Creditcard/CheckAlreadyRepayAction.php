<?php

namespace App\Models\Chain\UserBill\Creditcard;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Chain\UserBill\Creditcard\CreateUserBillLogAction;

/**
 * 2.已还点击之后不可进行修改
 * Class CheckAlreadyRepayAction
 * @package App\Models\Chain\UserBill\Creditcard
 */
class CheckAlreadyRepayAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,您已经设为已还！', 'code' => 1001);
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
        if ($this->checkAlreadyRepay($this->params) == true) {
            $this->setSuccessor(new CreateUserBillLogAction($this->params));
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
    private function checkAlreadyRepay($params = [])
    {
        //已还账单不可进行修改
        if (!empty($params['billId'])) {
            //修改账单状态
            $billInfo = UserBillFactory::fetchBillInfoById($params['billId']);
            if ($billInfo && $billInfo['bill_status'] == 1) {
                return false;
            }
            return true;
        }

        return true;
    }
}
