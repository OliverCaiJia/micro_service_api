<?php

namespace App\Models\Chain\UserBill\Creditcard;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Chain\UserBill\Creditcard\CreateUserBillRelAction;
use App\Strategies\UserBillStrategy;

/**
 * 5.获取添加或修改账单信息
 * Class FetchUserBillAction
 * @package App\Models\Chain\UserBill\Creditcard
 */
class FetchUserBillAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,获取账单信息失败！', 'code' => 1006);
    private $params = array();
    protected $data = array();

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
        if ($this->fetchUserBill($this->params) == true) {
            return $this->data;
        } else {
            return $this->error;
        }
    }


    /**
     * @param $params
     * @return array
     */
    private function fetchUserBill($params)
    {
        $billInfo = UserBillFactory::fetchBillInfoById($params['bill_id']);

        //数据处理
        $info = UserBillStrategy::getBillInfo($billInfo);
        $this->data = $info;

        return $info;
    }


}


