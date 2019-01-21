<?php

namespace App\Models\Chain\UserBill\Product;

use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;

/**
 * 3.验证期数与当前期数是否与数据库中现存数据一致  不一致：重置
 *                                             一致：修改其他数据
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 *
 */
class CheckPeriodNumAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '验证期数失败！', 'code' => 1003);
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
        if ($this->checkPeriodNum($this->params) == true) {
            $this->setSuccessor(new CreateUserBillPlatformAction($this->params));
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
    private function checkPeriodNum($params)
    {
        //是否存在,网贷平台id,验证期数只对修改有用
        if (empty($params['billProductId'])) {
            return true;
        }

        //根据平台id获取账单数 = 期数, 并设置一个标识
        $platform = UserBillPlatformFactory::fetchPlatformInfoById($params['billProductId']);
        //全部修改为已还之后 不可进行编辑
        if($params['already_count'] == $platform['product_period_total']) {
            $this->error = array('error' => RestUtils::getErrorMessage(2307), 'code' => 2307);
            return false;
        }


        if ($params['productPeriodTotal'] != $platform['product_period_total']) {
            //总期数改变，重置
            $params['is_total_become'] = 1;
        } else {
            //总期数没改变，只想应的改变账单其他值
            $params['is_total_become'] = 0;
        }

        $this->params['is_total_become'] = $params['is_total_become'];

        return true;
    }


}


