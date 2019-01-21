<?php

namespace App\Models\Chain\UserBill\Product;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;

/**
 * 4.修改或建立平台表
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 *
 */
class CreateUserBillPlatformAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '抱歉，创建网贷平台失败！', 'code' => 1004);
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
        if ($this->createBillPlatform($this->params) == true) {
            $this->setSuccessor(new CreateUserBillLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * 创建或修改userBillPlatform信息
     * @param $params
     * @return bool
     */
    private function createBillPlatform($params)
    {
        //创建或修改userBillPlatform信息
        $res = UserBillPlatformFactory::createOrUpdateProduct($params);
        if($res)
        {
            $params['billProductId'] = $res['id'];
            $this->params = $params;
            SLogger::getStream()->info('res', ['message' => $res, 'code' => 82340]);
            return true;
        }

        return false;
    }


}


