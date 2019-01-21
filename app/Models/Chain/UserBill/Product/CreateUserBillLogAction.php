<?php

namespace App\Models\Chain\UserBill\Product;

use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;

/**
 * 5.循环建立账单流水表
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 *
 */
class CreateUserBillLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '创建账单流水失败！', 'code' => 1005);
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
        if ($this->createUserBillLog($this->params) == true) {
            $this->setSuccessor(new CreateUserBillAction($this->params));
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
    private function createUserBillLog($params)
    {
        //dd($params);
        $res = '';
        //参数传过来的当前期数
        $periodNumParams = $params['productBillPeriodNum'];
        $params['bill_platform_id'] = $params['billProductId'];
        //如果还款日 < 当前日期  则为下个月; 还款日 >= 当前日期 则为本月
        //当前的年月
        $now_year_month = date('Y-m', time());
        //当前日
        $now_day = date('j', time());
        //当前时间从下个月开始
        if (intval($params['productRepayDay']) < intval($now_day)) {
            $now_year_month = date('Y-m', strtotime("$now_year_month +1 month"));
        }


        //根据期数创建账单
        for ($i = 0; $i < $params['productPeriodTotal']; $i++) {
            //当前期数
            $params['product_bill_period_num'] = $i + 1;
            $periodNum = $params['product_bill_period_num'];
            //对当前期数进行处理
            if ($periodNumParams > $periodNum) {
                //账单日期
                //本月月初为账单日期
                $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . '- ' . $periodNum . 'month'));
                //还款日期
                $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . '- ' . $periodNum . 'month'));
                //0待还 1已还 2未还
                $params['bill_status'] = 1;
            } else {
                //还款日期
                $countPeriod = $periodNum - $periodNumParams;
                //0待还 1已还 2未还
                $params['bill_status'] = $countPeriod == 0 ? 0 : 2;
                //账单日期
                //本月月初为账单日期
                $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . '+' . $countPeriod . ' month'));
                $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . '+ ' . $countPeriod . ' month'));
            }
            //创建账单信息
            $params['bill_cycle'] = $bill_cycle = DateUtils::formatDateToLeftdata($params['bill_time']) . '-' . DateUtils::formatDateToLeftdata($params['repay_time']);
            //网贷
            $params['bill_type'] = 2;
            $this->params['bill_type'] = $params['bill_type'];

            $res = UserBillFactory::createUserBillLog($params);
        }
        //SLogger::getStream()->info('userBillLog', ['message' => $res]);
        return $res ? true : false;
    }


}


