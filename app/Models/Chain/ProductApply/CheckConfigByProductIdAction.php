<?php
namespace App\Models\Chain\ProductApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserCreditProductConfig;
use App\Models\Chain\ProductApply\CheckIsApplyLogAction;

class CheckConfigByProductIdAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '产品配置表判断失败!', 'code' => 8001);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     * 1.接收产品id 判断是否存在于产品配置表中
     */
    public function handleRequest()
    {
        if ($this->checkConfigByProductId($this->params) == true) {
            $this->setSuccessor(new CheckIsApplyLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return true;
        }
    }

    /**
     * @param $params
     * 传值productId是否存在于config中
     */
    private function checkConfigByProductId($params)
    {
        $productConfigArr = UserCreditProductConfig::select(['product_id'])
            ->where(['status' => 0])
            ->pluck('product_id')
            ->toArray();
        if (in_array($params['productId'], $productConfigArr)) {
            $configObj                = UserCreditProductConfig::select(['id', 'credits'])
                ->where(['product_id' => $params['productId'], 'status' => 0])
                ->first();
            $this->params['configId'] = $configObj->id;
            $this->params['credits']  = intval($configObj->credits);
            return true;
        }
        return false;
    }


}
