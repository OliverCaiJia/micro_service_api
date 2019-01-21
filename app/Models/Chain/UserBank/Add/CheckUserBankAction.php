<?php

namespace App\Models\Chain\UserBank\Add;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Chain\UserBank\Add\CheckYibaoCardAction;

/**
 * 2.验证银行卡是否存在
 * Class CheckUserinfoAction
 * @package App\Models\Chain\Payment\Bankcard
 *
 */
class CheckUserBankAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '银行卡已存在！', 'code' => 10002);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 验证银行卡是否存在
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->checkUserBank($this->params) == true) {
            $this->setSuccessor(new CheckYibaoCardAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 验证银行卡是否存在
     * @param $params
     * @return bool
     */
    private function checkUserBank($params)
    {
        $userbank = UserBankCardFactory::fetchUserBankByAccount($params);
        //存在已添加过的银行卡
        if ($userbank) {
            return false;
        }
        return true;
    }
}
