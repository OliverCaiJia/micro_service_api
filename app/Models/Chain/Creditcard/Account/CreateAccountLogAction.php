<?php

namespace App\Models\Chain\Creditcard\Account;

use App\Models\Factory\CreditcardAccountFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Creditcard\Account\CreateOrUpdateAccountAction;

/**
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 * 3.插入流水sd_bank_creditcard_account_log表
 */
class CreateAccountLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,插入信用卡账户流水失败！', 'code' => 1002);
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
        if ($this->createAccountLog($this->params) == true) {
            $this->setSuccessor(new CreateOrUpdateAccountAction($this->params));
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
    private function createAccountLog($params)
    {
        return CreditcardAccountFactory::createAccountLog($params);
    }
}
