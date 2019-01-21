<?php

namespace App\Models\Chain\UserBill\Creditcard;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Chain\UserBill\Creditcard\FetchUserBillAction;

/**
 * 5.创建或修改关联表
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 *
 */
class CreateUserBillRelAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,关联表修改失败！', 'code' => 1005);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     *
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->createUserBillRel($this->params) == true) {
            $this->setSuccessor(new FetchUserBillAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function createUserBillRel($params)
    {
        $rel = UserBillFactory::createUserBillRel($params);

        if (!$rel) {
            return false;
        }

        return true;
    }


}


