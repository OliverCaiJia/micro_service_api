<?php

namespace App\Models\Chain\Creditcard\Account;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CreditcardAccountFactory;
use App\Models\Chain\Creditcard\Account\CreateAccountLogAction;

/**
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 * 2.验证该信用卡账户是否存在
 */
class CheckIsAccountAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,accountId参数有问题！', 'code' => 1001);
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
        if ($this->checkIsAccount($this->params) == true) {
            $this->setSuccessor(new CreateAccountLogAction($this->params));
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
    private function checkIsAccount($params)
    {
        $isAccount = CreditcardAccountFactory::checkIsAccount($params);

        //账户存在与否  必须与accountId对应上
        if (empty($params['accountId']) && $isAccount) {
            return false;
        } elseif (!$isAccount && !empty($params['accountId'])) {
            return false;
        }

        return true;
    }
}
