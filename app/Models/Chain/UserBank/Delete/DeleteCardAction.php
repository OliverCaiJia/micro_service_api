<?php

namespace App\Models\Chain\UserBank\Delete;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBankCardFactory;

/**
 * 2.删除银行卡
 * Class CheckUserinfoAction
 * @package App\Models\Chain\Payment\Bankcard
 *
 */
class DeleteCardAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '删除银行卡失败！', 'code' => 10002);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 删除银行卡
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->deleteCard($this->params) == true) {
            $this->setSuccessor(new UpdateCardDefaultAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 删除银行卡
     * @param $params
     * @return bool
     */
    private function deleteCard($params)
    {
        $del = UserBankCardFactory::deleteUserBankById($params);

        return $del;
    }
}
