<?php

namespace App\Models\Chain\UserBill\Product;

use App\Helpers\RestUtils;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;

/**
 * 1.修改时验证：超过最后还款月份不可进行修改
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 *
 */
class CheckBillRepayTimeAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,超过最后还款月份不可进行修改！', 'code' => 1001);
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
        if ($this->checkBillRepayTime($this->params) == true) {
            $this->setSuccessor(new CreateUserBillPlatformLogAction($this->params));
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
    private function checkBillRepayTime($params)
    {
        //当前期数<总期数
        if ($params['productBillPeriodNum'] > $params['productPeriodTotal']) {
            $this->error = array('error' => RestUtils::getErrorMessage(2306), 'code' => 2306);
            return false;
        }

        //创建
        if (empty($params['billProductId'])) {
            return true;
        }

        //修改时验证：超过最后还款月份不可进行修改
        //根据billProductId 查询网贷对应账单
        $billIds = UserBillFactory::fetchRelBillIdsById($params['billProductId']);
        //查询未删除、最晚还款时间
        $repayTime = UserBillFactory::fetchProductRepayTimeByBillIds($billIds);
        //当前时间
        $now = date('Y-m-d', time());
        //当前时间>最晚还款时间、不可以进行修改
        if (strtotime($now) > strtotime($repayTime)) {
            return false;
        }

        $alreadyCount = UserBillFactory::fetchBillsAlreadyCount($billIds);
        $this->params['already_count'] = $alreadyCount;

        //可以进行修改
        return true;
    }


}


