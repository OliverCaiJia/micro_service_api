<?php

namespace App\Models\Chain\UserBank\LastPay;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBankCardFactory;

/**
 * 2.设置本次支付卡
 * Class CheckUserinfoAction
 * @package App\Models\Chain\Payment\Bankcard
 *
 */
class UpdateLastStatusAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '设置支付卡失败！', 'code' => 10002);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 设置本次支付卡
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateLastStatus($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }


    /**
     * 设置本次支付卡
     * @param $params
     * @return bool
     */
    private function updateLastStatus($params)
    {
        //修改支付卡片状态
        $cardLastStatus = UserBankCardFactory::updateCardLastStatusById($params);

        return $cardLastStatus;
    }
}
