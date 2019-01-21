<?php

namespace App\Models\Chain\UserBank\Add;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBankCardFactory;

/**
 * 6.添加或修改sd_user_banks用户银行卡信息
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 *
 */
class UpdateUserBanksAction extends AbstractHandler
{
    private $params = array();
    protected $data = array();
    protected $error = array('error' => '用户银行卡修改有误！', 'code' => 10007);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 添加或修改sd_user_banks用户银行卡信息
     * @return array|bool
     *
     */
    public function handleRequest()
    {
        if ($this->updateUserBanks($this->params) == true) {
            return $this->data;
        } else {
            return $this->error;
        }
    }

    /**
     * 添加或修改sd_user_banks用户银行卡信息
     * @param $params
     * @return bool
     *
     */
    private function updateUserBanks($params = [])
    {
        $params['card_default'] = 0;
        $params['card_last_status'] = 0;

        //储蓄卡是否存在
        $userBank = UserBankCardFactory::fetchCarddefaultById($params['userId']);
        //添加第一张储蓄卡 则默认为默认储蓄卡
        if ($params['cardType'] == 1 && empty($userBank)) {
            $params['card_default'] = 1;
        }

        $res = UserBankCardFactory::createOrUpdateUserBank($params);

        $this->data['id'] = $res['id'];

        return $res;
    }

}
