<?php

namespace App\Models\Chain\UserBank\Defaultcard;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBankCardFactory;

/**
 * 2.设置最新的默认储蓄卡
 * Class CheckUserinfoAction
 * @package App\Models\Chain\Payment\Bankcard
 *
 */
class UpdateCardDefaultAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '默认卡设置失败！', 'code' => 10002);

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 设置最新的默认储蓄卡
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateCardDefaul($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }


    /**
     * 设置最新的默认储蓄卡
     * @param $params
     * @return bool
     */
    private function updateCardDefaul($params)
    {
        //设置默认储蓄卡
        $set = UserBankCardFactory::setDefaultById($params);

        return $set;
    }
}
