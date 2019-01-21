<?php

namespace App\Models\Chain\UserSign;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\CreditFactory;
use App\Models\Chain\UserSign\UpdateUserCreditAction;

/**
 * 第二步:记录积分增加流水
 */
class CreateCreditLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,用户签到流水记录失败！', 'code' => 1002);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第二步:记录积分增加流水
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->createCreditLog($this->params)) {
            $this->setSuccessor(new UpdateUserCreditAction($this->params));
            $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 创建记录
     * @param $params
     * @return bool
     */
    private function createCreditLog($params)
    {
        return CreditFactory::createAddCreditLog($params);
    }
}
