<?php
namespace App\Models\Chain\Apply;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserFactory;

class UpdateProductCountAction extends AbstractHandler
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
        if ($this->updateProductCount($this->params) == true) {
            return $this->params['urlArr'];
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function updateProductCount($params)
    {
        $productId = $params['productId'];
        $platformId = $params['platformId'];
        $userId = $params['userId'];
        $type = $params['type'];

        //单个平台点击立即申请数据统计
        PlatformFactory::updatePlatformClick($platformId);
        //单个产品点击立即申请数据统计
        ProductFactory::updateProductClick($productId);
        //单个用户针对所有产品点击立即申请数据统计
        switch ($type) {
            case 1:
                // 非合作点击网页
                UserFactory::updateUserCount($userId,'click_count');
                break;
            case 2:
                // 非合作点击微信
                UserFactory::updateUserCount($userId,'weixin_count');
                break;
            case 3:
                // 非合作点击app
                UserFactory::updateUserCount($userId,'app_count');
                break;
            case 4:
                // 合作店家借款
                //用户借款记录表数据统计
                PlatformFactory::createPlatgormLoanLog($platformId,$userId);

                UserFactory::updateUserCount($userId,'h5_count');
                break;
            default:

                break;
        }

        return true;
    }
}