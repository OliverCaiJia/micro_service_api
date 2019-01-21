<?php

namespace App\Models\Chain\UserBank\Delete;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBankCardFactory;

/**
 * 3.若删除默认银行卡，则默认下一张为默认卡
 * Class CheckUserinfoAction
 * @package App\Models\Chain\Payment\Bankcard
 *
 */
class UpdateCardDefaultAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '默认银行卡设置出错！', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 若删除默认银行卡，则默认下一张为默认卡
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateCardDefault($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }


    /**
     * 若删除默认银行卡，则默认下一张为默认卡
     * @param $params
     * @return bool
     */
    private function updateCardDefault($params)
    {
        //删除储蓄卡时，查询是否含有默认储蓄卡
        $defaultIds = UserBankCardFactory::getDefaultBankCardIdById($params['userId']);
        //没有默认储蓄卡，进行查询；如果删除信用卡时，不需要设置默认储蓄卡
        if ($params['cardType'] == 1 && empty($defaultIds)) {
            //设置默认的储蓄卡
            $params['userbankId'] = UserBankCardFactory::fetchCarddefaultIdByTime($params['userId']);
            $default = UserBankCardFactory::setDefaultById($params);
            if (!$params['userbankId'] || !$default) {
                return false;
            }
        }
        return true;
    }
}
