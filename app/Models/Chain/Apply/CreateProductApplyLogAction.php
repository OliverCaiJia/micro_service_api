<?php
namespace App\Models\Chain\Apply;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\DeliveryFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;

class CreateProductApplyLogAction extends AbstractHandler
{
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
        if ($this->createProductApplyLog($this->params) == true) {
            $this->setSuccessor(new UpdateProductCountAction($this->params));
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
    private function createProductApplyLog($params)
    {
        $productId = $params['productId'];
        $userId = $params['userId'];
        //单个产品点击立即申请流水统计
        //获取用户信息
        $userArr = UserFactory::fetchUserNameAndMobile($userId);
        //获取产品信息
        $productArr = ProductFactory::fetchProductname($productId);
        //获取渠道id
        $deliveryId = DeliveryFactory::fetchDeliveryId($userId);
        //获取渠道信息
        $deliveryArr = DeliveryFactory::fetchDeliveryArray($deliveryId);

        if(empty($userArr) || empty($productArr) || empty($deliveryArr) || empty($deliveryId)) {
            return false;
        }

        $res = DeliveryFactory::createProductApplyLog($userId,$userArr,$productArr,$deliveryArr);
        
        return $res;
    }
}