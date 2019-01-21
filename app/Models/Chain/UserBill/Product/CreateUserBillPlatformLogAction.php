<?php

namespace App\Models\Chain\UserBill\Product;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;

/**
 * 2.建立平台流水表
 * Class CreateUserBillPlatformLogAction
 * @package App\Models\Chain\UserBill\Product
 */
class CreateUserBillPlatformLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,平台流水添加失败！', 'code' => 1002);
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
        if ($this->createBillPlatformLog($this->params) == true) {
            $this->setSuccessor(new CheckPeriodNumAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 创建流失记录
     * @param $params
     * @return bool
     */
    private function createBillPlatformLog($params)
    {
//        SLogger::getStream()->info('platformLogparams', ['message' => $params, 'code' => 67762]);
        //流水表根据每次的修改信息重新创建
        $log = UserBillPlatformFactory::createCreditcardLog($params);
        SLogger::getStream()->info('log', ['message' => $log, 'code' => 67722]);

        return $log ? true : false;
    }


}


