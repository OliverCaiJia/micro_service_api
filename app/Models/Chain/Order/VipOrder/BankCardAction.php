<?php

namespace App\Models\Chain\Order\VipOrder;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\UserBankCardFactory;
use App\Strategies\UserVipStrategy;

class BankCardAction extends AbstractHandler
{

    private $params = array();
    private $backInfo = array();
    protected $error = array('error' => '设置银行卡失败！', 'code' => 1005);

    public function __construct($params, $backInfo)
    {
        $this->params = $params;
        $this->backInfo = $backInfo;
    }

    /**
     * 第四步:设置银行卡
     * @return array
     */
    public function handleRequest()
    {
        if ($this->bankCard($this->params)) {
            return $this->backInfo;
        } else {
            return $this->error;
        }
    }

    /**
     * 设置银行卡
     */
    private function bankCard($params = [])
    {
        if ($params['pay_type'] == 3) {
            UserBankCardFactory::setLastestUsedCard($params['bankcard_id'], $params['user_id']);
        }

        return true;
    }

}
