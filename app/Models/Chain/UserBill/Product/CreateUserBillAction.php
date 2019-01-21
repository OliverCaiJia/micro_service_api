<?php

namespace App\Models\Chain\UserBill\Product;

use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;

/**
 * 6.循环建立账单表
 * Class FetchDeviceIdAction
 * @package App\Models\Chain\Creditcard\Bill
 *
 */
class CreateUserBillAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '抱歉，创建账单流水失败！', 'code' => 1006);
    private $params = array();
    protected $data = array();

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
        if ($this->createUserBill($this->params) == true) {
            return $this->data;
        } else {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function createUserBill($params)
    {
        $ret = '';
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

        //创建期数
        if (!isset($params['is_total_become'])) {
            for ($i = 0; $i < $params['productPeriodTotal']; $i++) {
                //当前期数
                $params['product_bill_period_num'] = $i + 1;
                $periodNum = $params['product_bill_period_num'];
                //对当前期数进行处理
                if ($periodNumParams > $periodNum) {
                    $periodNum = $periodNumParams - $params['product_bill_period_num'];
                    //账单日期
                    //本月月初为账单日期
                    $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' -' . $periodNum . 'month'));
                    //还款日期
                    $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' -' . $periodNum . 'month'));
                    //0待还 1已还 2未还
                    $params['new_bill_status'] = 1;
                } else {
                    //还款日期
                    $countPeriod = $periodNum - $periodNumParams;
                    //0待还 1已还 2未还
                    $params['new_bill_status'] = $countPeriod == 0 ? 0 : 2;
                    //账单日期
                    //本月月初为账单日期
                    $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' +' . $countPeriod . ' month'));
                    $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' +' . $countPeriod . ' month'));
                }

                //账单周期
                $params['bill_cycle'] = $bill_cycle = DateUtils::formatDateToLeftdata($params['bill_time']) . '-' . DateUtils::formatDateToLeftdata($params['repay_time']);
                $data['bill_id'] = UserBillFactory::createOrUpdateUserBill($params);

                $data['bill_platform_id'] = $params['billProductId'];

                //创建中间表数据
                $ret = UserBillFactory::createUserBillRel($data);
            }
        }

        //总期数变动
        if (isset($params['is_total_become'])) {
            //总期数：变  总期数变动不管每期数直接进行重置
            if ($params['is_total_become'] == 1) {
                //将账单数据进行重置
                //获取平台下的所有账单id
                $bills = UserBillFactory::fetchRelBillIdsById($params['billProductId']);
                //数据库存在这个平台所有的账单数
                $num = count($bills);
                //总期数变小，改动原来的，多余的删除
                if ($num > $params['productPeriodTotal']) {
                    foreach ($bills as $key => $bill) {
                        //当前期数
                        $params['product_bill_period_num'] = $key + 1;

                        $periodNum = $params['product_bill_period_num'];
                        //对当前期数进行处理
                        if ($periodNumParams > $periodNum) {
                            $periodNum = $periodNumParams - $params['product_bill_period_num'];
                            //账单日期
                            //本月月初为账单日期
                            $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' -' . $periodNum . 'month'));
                            //还款日期
                            $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' -' . $periodNum . 'month'));
                            //0待还 1已还 2未还
                            $params['new_bill_status'] = 1;
                        } else {
                            //还款日期
                            $countPeriod = $periodNum - $periodNumParams;
                            //0待还 1已还 2未还
                            $params['new_bill_status'] = $countPeriod == 0 ? 0 : 2;
                            //账单日期
                            //本月月初为账单日期
                            $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' +' . $countPeriod . ' month'));
                            $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' +' . $countPeriod . ' month'));

                        }

                        //如果大于了当前期数,将其余的进行逻辑删除
                        if ($key >= $params['productPeriodTotal']) {
                            //删除
                            $params['is_delete'] = 1;
                        }
                        //创建账单
                        $params['billId'] = $bill;
                        //账单周期
                        $params['bill_cycle'] = $bill_cycle = DateUtils::formatDateToLeftdata($params['bill_time']) . '-' . DateUtils::formatDateToLeftdata($params['repay_time']);
                        //重新修改数据，重新生成时间
                        $data['bill_id'] = UserBillFactory::createOrUpdateUserBill($params);
                        $data['bill_platform_id'] = $params['billProductId'];

                        //创建中间表数据
                        $ret = UserBillFactory::createUserBillRel($data);
                    }

                } else {
                    //总期数变大 改动原来的，生成多余的
                    foreach ($bills as $key => $bill) {
                        //当前期数
                        $params['product_bill_period_num'] = $key + 1;

                        $periodNum = $params['product_bill_period_num'];
                        //对当前期数进行处理
                        if ($periodNumParams > $periodNum) {
                            $periodNum = $periodNumParams - $params['product_bill_period_num'];
                            //账单日期
                            //本月月初为账单日期
                            $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' -' . $periodNum . 'month'));
                            //还款日期
                            $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' -' . $periodNum . 'month'));
                            //0待还 1已还 2未还
                            $params['new_bill_status'] = 1;
                        } else {
                            //还款日期
                            $countPeriod = $periodNum - $periodNumParams;
                            //0待还 1已还 2未还
                            $params['new_bill_status'] = $countPeriod == 0 ? 0 : 2;
                            //账单日期
                            //本月月初为账单日期
                            $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' +' . $countPeriod . ' month'));
                            $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' +' . $countPeriod . ' month'));
                        }

                        //创建账单
                        $params['billId'] = $bill;
                        //账单周期
                        $params['bill_cycle'] = $bill_cycle = DateUtils::formatDateToLeftdata($params['bill_time']) . '-' . DateUtils::formatDateToLeftdata($params['repay_time']);
                        $data['bill_id'] = UserBillFactory::createOrUpdateUserBill($params);
                        $data['bill_platform_id'] = $params['billProductId'];

                        //创建中间表数据
                        $ret = UserBillFactory::createUserBillRel($data);
                    }

                    //对多出来的进行单独处理
                    $over = $params['productPeriodTotal'] - $num;
                    for ($i = 0; $i < $over; $i++) {
                        //新的当前期数
                        $params['product_bill_period_num'] = $num + $i + 1;
                        $periodNum = $params['product_bill_period_num'];
                        //往后推迟还款日期
                        $countPeriod = $params['product_bill_period_num'] - $periodNumParams;

                        if ($periodNumParams > $periodNum) {
                            //0待还 1已还 2未还
                            $params['new_bill_status'] = 1;
                        } else {
                            //0待还 1已还 2未还
                            $params['new_bill_status'] = $countPeriod == 0 ? 0 : 2;
                        }

                        //本月月初为账单日期
                        $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' +' . $countPeriod . ' month'));
                        $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' +' . $countPeriod . ' month'));

                        //创建账单
                        //重置billId
                        $params['billId'] = 0;
                        //账单周期
                        $params['bill_cycle'] = $bill_cycle = DateUtils::formatDateToLeftdata($params['bill_time']) . '-' . DateUtils::formatDateToLeftdata($params['repay_time']);
                        $data['bill_id'] = UserBillFactory::createOrUpdateUserBill($params);
                        $data['bill_platform_id'] = $params['billProductId'];
                        //创建中间表数据
                        $ret = UserBillFactory::createUserBillRel($data);
                    }
                }
            } elseif ($params['is_total_become'] == 0) {
                //总期数:没变　之前的账单信息不作调整,只对当前和之后的账单进行调整
                //修改的当前期数和现在的当前期数是否相等　　　不一样：重置　　　一样：之前操作的不修改,之后的进行修改
                //获取平台下的所有账单id
                $billAllIds = UserBillFactory::fetchRelBillIdsById($params['bill_platform_id']);
                //未删除的账单id
                $bills = UserBillFactory::fetchBillsNotDelete($billAllIds);

                //当前月份还款日
                $data['repay_time'] = date('Y-m-' . $params['productRepayDay'], time());
                $data['billIds'] = $bills;
                $data['userId'] = $params['userId'];
                //获取该平台下所有账单的还款日期
                $repayTimes = UserBillFactory::fetchRepayBillTimes($data['billIds']);
                if (!in_array($data['repay_time'], $repayTimes)) {
                    $data['repay_time'] = $repayTimes[0];
                }
                $periodBill = UserBillFactory::fetchProductById($data);
                $periodNum = $periodBill['product_bill_period_num'];

                if ($periodNum != 0) {
                    //当前期数变  需要重置
                    if ($params['productBillPeriodNum'] != $periodNum) // 重置
                    {
                        foreach ($bills as $key => $bill) {
                            //当前期数
                            $params['product_bill_period_num'] = $key + 1;

                            $periodNum = $params['product_bill_period_num'];
                            //对当前期数进行处理
                            if ($periodNumParams > $periodNum) {
                                $periodNum = $periodNumParams - $params['product_bill_period_num'];
                                //本月月初为账单日期
                                $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' -' . $periodNum . 'month'));
                                //还款日期
                                $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' -' . $periodNum . 'month'));
                                //0待还 1已还 2未还
                                $params['new_bill_status'] = 1;
                            } else {
                                //还款日期
                                $countPeriod = $periodNum - $periodNumParams;
                                //0待还 1已还 2未还
                                $params['new_bill_status'] = $countPeriod == 0 ? 0 : 2;
                                //账单日期
                                //本月月初为账单日期
                                $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' +' . $countPeriod . ' month'));
                                $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' +' . $countPeriod . ' month'));
                            }

                            //总期数不变的情况,就不考虑之前有多少个
                            //创建账单
                            $params['billId'] = $bill;
                            //账单周期
                            $params['bill_cycle'] = $bill_cycle = DateUtils::formatDateToLeftdata($params['bill_time']) . '-' . DateUtils::formatDateToLeftdata($params['repay_time']);
                            $data['bill_id'] = UserBillFactory::createOrUpdateUserBill($params);
                            $data['bill_platform_id'] = $params['billProductId'];

                            //创建中间表数据
                            $ret = UserBillFactory::createUserBillRel($data);
                        }

                    } else {
                        //总期数和当前期数都没变
                        foreach ($bills as $key => $bill) {
                            //改动日期
                            //当前期数
                            $params['product_bill_period_num'] = $key + 1;

                            $periodNum = $params['product_bill_period_num'];
                            //对当前期数进行处理
                            if ($periodNumParams > $periodNum) {
                                $periodNum = $periodNumParams - $params['product_bill_period_num'];
                                //账单日期
                                //本月月初为账单日期
                                $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' -' . $periodNum . 'month'));
                                //还款日期
                                $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' -' . $periodNum . 'month'));
                                //0待还 1已还 2未还
                                //$params['new_bill_status'] = 1;
                            } else {
                                //还款日期
                                $countPeriod = $periodNum - $periodNumParams;
                                //0待还 1已还 2未还
                                //$params['new_bill_status'] = $countPeriod == 0 ? 0 : 2;
                                //本月月初为账单日期
                                $params['bill_time'] = date('Y-m-' . '01', strtotime($now_year_month . ' +' . $countPeriod . ' month'));
                                $params['repay_time'] = date('Y-m-' . $params['productRepayDay'], strtotime($now_year_month . ' +' . $countPeriod . ' month'));
                            }

                            //创建账单
                            $params['billId'] = $bill;
                            //账单周期
                            $params['bill_cycle'] = $bill_cycle = DateUtils::formatDateToLeftdata($params['bill_time']) . '-' . DateUtils::formatDateToLeftdata($params['repay_time']);
                            $data['bill_id'] = UserBillFactory::updateUserBill($params);
                            $data['bill_platform_id'] = $params['billProductId'];

                            //创建中间表数据
                            $ret = UserBillFactory::createUserBillRel($data);
                        }

                    }
                }
            }

        }

        $this->data['billProductId'] = $params['billProductId'];


        return $ret ? true : false;
    }


}


