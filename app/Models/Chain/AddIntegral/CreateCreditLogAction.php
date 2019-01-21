<?php

namespace App\Models\Chain\AddIntegral;

use App\Models\Factory\CreditFactory;
use App\Models\Chain\AbstractHandler;
use App\Models\Chain\AddIntegral\UpdateCreditAction;

class CreateCreditLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,积分兑换流水插入数据失败,可能积分数不为正数！', 'code' => 6002);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed]
     * 2.加积分流水
     */
    public function handleRequest()
    {
        if ($this->createUserCreditLog($this->params) == true) {
            $this->setSuccessor(new UpdateCreditAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * @param $params
     * @return mixed
     * 创建积分流水
     */
    private function createUserCreditLog($params)
    {
        //需要参数
        $data['user_id'] = $params['userId'];
        $data['type'] = $params['typeNid'];
        $data['income'] = $params['score'];
        $data['remark'] = $params['remark'];

        if (empty($data['income']) || $data['income'] < 0) {
            return false;
        }

        return CreditFactory::createAddCreditLog($data);
    }

}