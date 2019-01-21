<?php

namespace App\Models\Chain\UserBank\Add;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Chain\UserBank\Add\UpdateUserBanksAction;

/**
 * 5.验证更换信用卡
 * Class CheckUserinfoAction
 * @package App\Models\Chain\Payment\Bankcard
 *
 */
class CheckReplaceAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '更换银行卡出错！', 'code' => 10006);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 验证更换信用卡
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->checkReplace($this->params) == true) {
            $this->setSuccessor(new UpdateUserBanksAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 验证更换信用卡
     * @param $params
     * @return bool
     */
    private function checkReplace($params)
    {
        if (empty($params['replace']) || $params['replace'] != 'replace') {
            return true;
        }
        //逻辑删除更换银行卡
        $replace = UserBankCardFactory::deleteUserBankById($params);

        return $replace;
    }
}
