<?php

namespace App\Models\Chain\UserBank\Add;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\UserBank\Add\FetchBankcardInfoAction;
use App\Services\Core\Payment\PaymentService;

/**
 * Class SendImageToQiniuAction
 * @package App\Models\Chain\UserIdentity\IdcardFront
 * 2.调用易宝验证银行卡号
 */
class CheckYibaoCardAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '银行卡号无效或银行卡类型错误！', 'code' => 10003);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 调用易宝验证银行卡号
     */
    public function handleRequest()
    {
        if ($this->checkYibaoCard($this->params) == true) {
            $this->setSuccessor(new FetchBankcardInfoAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool
     * 调用易宝验证银行卡号
     */
    private function checkYibaoCard($params = [])
    {
        //调用易宝支付卡号验证
        $data['cardno'] = $params['account'];
        $ret = PaymentService::i($params['shadow_nid'])->extraInterface($data);
        //银行卡号无效
        if (empty($ret) || $ret['isvalid'] == 0) {
            return false;
        }
        //银行卡类型不一致
        if ($params['cardType'] != $ret['cardtype']) {
            return false;
        }

        $this->params['bankcode'] = $ret['bankcode'];
        $this->params['bankname'] = $ret['bankname'];
        $this->params['cardno'] = $ret['cardno'];
        $this->params['cardtype'] = $ret['cardtype'];
        return true;
    }

}
