<?php

namespace App\Models\Chain\UserBank\LastPay;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBankCardFactory;

/**
 * 1.取消上次默认支付卡
 * Class CheckUserinfoAction
 * @package App\Models\Chain\Payment\Bankcard
 *
 */
class DeleteLastPayAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '取消支付卡失败！', 'code' => 10001);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 取消上次默认支付卡
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->deleteLastPay($this->params) == true) {
            $this->setSuccessor(new UpdateLastStatusAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 取消上次默认支付卡
     * @param $params
     * @return bool
     */
    private function deleteLastPay($params)
    {
        //查询是否存在上次使用的支付卡
        $params['ids'] = UserBankCardFactory::fetchLastPaymentById($params);
        if (empty($params['ids'])) {
            return false;
        }
        //取消上次支付状态
        $updateLast = UserBankCardFactory::deleteCardLastStatusByIds($params);

        return $updateLast;
    }
}
