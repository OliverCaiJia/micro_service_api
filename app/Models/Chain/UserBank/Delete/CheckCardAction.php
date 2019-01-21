<?php

namespace App\Models\Chain\UserBank\Delete;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBankCardFactory;

/**
 * 1.只剩最后一张储蓄卡，不允许删除
 * Class CheckUserinfoAction
 * @package App\Models\Chain\Payment\Bankcard
 *
 */
class CheckCardAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '最后一张卡不允许删除！', 'code' => 10001);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 只剩最后一张储蓄卡，不允许删除
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->checkCard($this->params) == true) {
            $this->setSuccessor(new DeleteCardAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 只剩最后一张储蓄卡，不允许删除
     * @param $params
     * @return bool
     */
    private function checkCard($params)
    {
        //只剩最后一张储蓄卡，不允许删除
        $savingCount = UserBankCardFactory::fetchSavingCountById($params);
        //银行储蓄卡只支持更换
        if ($params['cardType'] == 1 && $savingCount == 1) {
            return false;
        }

        return true;
    }
}
