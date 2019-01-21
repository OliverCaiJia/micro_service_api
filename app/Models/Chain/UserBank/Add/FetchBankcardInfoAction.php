<?php

namespace App\Models\Chain\UserBank\Add;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Chain\UserBank\Add\CheckTianfourAction;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 3.获取银行卡开户银行信息
 */
class FetchBankcardInfoAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '不支持该银行卡！', 'code' => 10004);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 获取银行卡开户银行信息
     */
    public function handleRequest()
    {
        if ($this->fetchBankcardInfo($this->params) == true) {
            $this->setSuccessor(new CheckTianfourAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 获取银行卡开户银行信息
     */
    private function fetchBankcardInfo($params = [])
    {
        $bankinfo = UserBankCardFactory::getBankInfoByBankcode($params['bankcode']);
        //银行不支持
        if (!$bankinfo) {
            return false;
        }
        $this->params['bankId'] = $bankinfo['id'];

        return true;
    }

}
