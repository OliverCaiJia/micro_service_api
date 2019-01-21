<?php

namespace App\Models\Chain\Payment\Bankcard;

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
            return true;
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
        //第一个添加的银行卡默认状态存在
        $userBank = UserBankCardFactory::fetchCarddefaultById($params['userId']);
        $params['card_default'] = 0;
        if (empty($userBank)) {
            $params['card_default'] = 1;
        }
        $res = UserBankCardFactory::createOrUpdateUserBank($params);

        return $res;
    }

}
