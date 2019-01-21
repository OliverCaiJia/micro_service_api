<?php

namespace App\Strategies;

use App\Constants\UserBillPlatformConstant;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * 用户账单策略
 * Class UserBillStrategy
 * @package App\Strategies
 */
class UserBillStrategy extends AppStrategy
{
    /**
     * 添加或修改账单信息
     * @param array $params
     * @return array
     */
    public static function getBillInfo($params = [])
    {
        $datas = [];
        //当前月份
//        $year_month = date('Y-m', time());
        //账单添加月份
//        $bill_year_month = date('Y-m', strtotime($params['bank_bill_time']));
        $datas['id'] = $params['id'];
        $bill_month = date('m', strtotime($params['bank_bill_time']));
        $datas['bill_month'] = $bill_month;
        $datas['bill_cycle'] = $params['bank_bill_cycle'];
        $datas['bill_status'] = $params['bill_status'];
        $datas['bill_money'] = $params['bill_money'];

        return $datas ? $datas : [];
    }

    /**
     * 根据平台id获取账单列表 数据处理
     * @param array $params
     * @return array
     */
    public static function getCreditcardBills($params = [])
    {
        $datas = [];
        foreach ($params as $key => $val) {
            $datas[$key]['id'] = $val['id'];
            $bill_month = date('m', strtotime($val['bank_bill_time']));
            $datas[$key]['bill_month'] = $bill_month;
            $datas[$key]['bill_cycle'] = $val['bank_bill_cycle'];
            $datas[$key]['bill_status'] = $val['bill_status'];
            $datas[$key]['bill_money'] = $val['bill_money'];
            $datas[$key]['is_import'] = $val['is_import'];
        }

        return $datas ? $datas : [];
    }

    /**
     * 信用卡已还账单 出账天数计算
     * @param array $bills
     * @return array
     */
    public static function getAlreadyOrder($bills = [])
    {
        foreach ($bills as $key => $value) {
            //标识账单信息是否存在 0不存在，1存在
            $billinfo_sign = $value['billinfo_sign'];

            //账单日
            $bank_bill_date = $value['bank_bill_date'];
            //还款日
            $bank_repay_day = $value['bank_repay_day'];
            //当前日期
            $now = date('Y-m-d', time());
            $strto_now = strtotime($now);
            $now_bill_time = date('Y-m-' . $bank_bill_date, time());

            //当前时间下的账单日
            $bank_bill_time = date('Y-m-' . $bank_bill_date, time());
            $strto_bank_bill_time = strtotime($bank_bill_time);
            //当前时间下的下一个账单日
            $next_bill_time = date('Y-m-d', strtotime("$bank_bill_time +1 month"));
            $strto_next_bill_time = strtotime($next_bill_time);
            //当前时间下的还款日

            //还款日与账单日求差
            $int_bank_repay_day = intval($bank_repay_day);
            $int_bank_bill_date = intval($bank_bill_date);
            $differ = bcsub($int_bank_repay_day, $bank_bill_date);
            //1.账单日<还款日&&账单日还款日相差17天以上，还款日期在同一个月；
            if ($int_bank_repay_day > $int_bank_bill_date && $differ >= UserBillPlatformConstant::BILL_DIFFER_VALUE) {
                $repay_time = date('Y-m-' . $value['bank_repay_day'], time());
            } else {
                //2.不满17天或还款日小于账单日 还款日期在下个月
                $now_year_month = date('Y-m', time());
                $repay_time = date('Y-m-' . $value['bank_repay_day'], strtotime("$now_year_month +1 month"));
            }

            $now_repay_time = $repay_time;
            $strto_repay_time = strtotime($repay_time);

            //当前时间下的下一个还款日
            $next_repay_time = date('Y-m-d', strtotime("$repay_time +1 month"));

            if ($strto_now < strtotime($now_bill_time)) {
                //当前时间下的账单日-1
                $bank_bill_time = date('Y-m-d', strtotime("$bank_bill_time -1 month"));
                $strto_bank_bill_time = strtotime($bank_bill_time);
                //当前时间下的下一个账单日-1
                $next_bill_time = date('Y-m-' . $bank_bill_date, time());
                $strto_next_bill_time = strtotime($next_bill_time);
                //当前时间下的还款日-1
                $repay_time = date('Y-m-d', strtotime("$repay_time -1 month"));
                $strto_repay_time = strtotime($repay_time);
                //当前时间下的下一个还款日
                $next_repay_time = date('Y-m-d', strtotime("$repay_time +1 month"));
            }
            //SLogger::getStream()->info('repay_time-'.$key, ['data' => $repay_time]);
            //数据库账单的还款日
            $mysql_repay_time = $value['repay_time'];
            $mysql_strto_repay_time = strtotime($mysql_repay_time);
            //数据库的账单日
            $mysql_bill_time = $value['bank_bill_time'];
            $mysql_strto_bill_time = strtotime($mysql_bill_time);

            //状态
            $bill_status = $value['bill_status'];
            //导入状态
            $bank_is_import = $value['bank_is_import'];
            //账单总数量
            $billCount = $value['billCount'];
            //|button_sign |int   | 按钮 【1设为已还,2更新账单可点击,3更新账单不可点击】 |
            //|bill_sign |int   | 账单逾期状态 【1逾期 ，2几天到期 ，,3当天到期，4几天前出账，5当天出账，6几天出账】 |

            //会变数据
            //账单日、还款日 数据库不存在账单、显示当前月份账单日、还款日
            $bills[$key]['bank_bill_time'] = !empty($mysql_bill_time) ? $mysql_bill_time : $now_bill_time;
            $bills[$key]['repay_time'] = !empty($mysql_repay_time) ? $mysql_repay_time : $now_repay_time;
            $bills[$key]['bill_money'] = !empty($value['bill_money']) ? $value['bill_money'] : '--';
            $bills[$key]['button_sign'] = 0;
            $bills[$key]['bill_sign'] = 0;
            $bills[$key]['differ_day'] = 0;

            //手动输入 网贷 已还
            if ($bank_is_import == 0 && $bill_status == 1 && $billinfo_sign == 1) {
                $bills[$key]['bill_money'] = '--';

            } elseif ($bank_is_import == 0 && $bill_status != 1 && $billinfo_sign == 1) {
                //手动输入 网贷 当月都是几天到期，小于当月的都是逾期
                //设为已还
                $bills[$key]['button_sign'] = 1;
                //金额
                $bills[$key]['bill_money'] = $value['bill_money'];
                //天数
                $differ_day = ($strto_now - $mysql_strto_repay_time) / 86400;
                $bills[$key]['differ_day'] = ceil(abs($differ_day)) . '';

                if ($strto_now >= $mysql_strto_bill_time && $strto_now <= $mysql_strto_repay_time) {
                    //几天到期、设为已还、1000
                    $bills[$key]['bill_sign'] = 2;
                    if ($strto_now == $mysql_strto_repay_time) {
                        //当天到期、设为已还、1000
                        $bills[$key]['bill_sign'] = 3;

                    }
                } elseif ($strto_now > $mysql_strto_repay_time) {
                    //逾期、设为已还、1000
                    $bills[$key]['bill_sign'] = 1;

                }
                //魔蝎导入
            } elseif ($bank_is_import == 1 && $billinfo_sign == 1) {
                // 1.2<1.10<1.28 未还
                if ($strto_now >= $strto_bank_bill_time && $strto_now <= $strto_repay_time && $bill_status == 0) {
                    //不属于当前账单周期内
                    if ($billCount == 0) {
                        //逾期、更新可点、1000
                        $bills[$key]['bill_sign'] = 1;
                        //设为已还
                        $bills[$key]['button_sign'] = 2;
                        //金额
                        $bills[$key]['bill_money'] = $value['bill_money'];
                        //天数
                        $differ_day = ($strto_now - $mysql_strto_repay_time) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';

                    } else {
                        if ($strto_now == $strto_repay_time && $bill_status == 0 && $billCount == 1) {
                            //当天到期、设为已还、1000
                            $bills[$key]['bill_sign'] = 3;
                            //更新可点击
                            $bills[$key]['button_sign'] = 1;
                            //金额
                            $bills[$key]['bill_money'] = $value['bill_money'];
                            //天数
                            $differ_day = ($strto_now - $strto_repay_time) / 86400;
                            $bills[$key]['differ_day'] = ceil($differ_day) . '';

                        } else {
                            //属于账单周期内
                            //几天到期、设为已还、1000
                            $bills[$key]['bill_sign'] = 2;
                            //设为已还
                            $bills[$key]['button_sign'] = 1;
                            //金额
                            $bills[$key]['bill_money'] = $value['bill_money'];
                            //天数
                            $differ_day = ($strto_repay_time - $strto_now) / 86400;
                            $bills[$key]['differ_day'] = ceil($differ_day) . '';
                        }
                    }

                } elseif ($strto_now >= $strto_bank_bill_time && $strto_now <= $strto_repay_time && $bill_status == 1) {
                    // 1.2<1.10<1.28 已还
                    //不属于当前账单周期范围内
                    if ($billCount == 0) {
                        //几天前出账、更新可点击、--
                        $bills[$key]['bill_sign'] = 4;
                        //更新可点击
                        $bills[$key]['button_sign'] = 2;
                        //金额
                        $bills[$key]['bill_money'] = '--';
                        //天数
                        $differ_day = ($strto_now - $strto_bank_bill_time) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';

                    } else {
                        if ($strto_now == $strto_bank_bill_time && $bill_status == 1) {
                            //当天出账、更新可点击、--
                            $bills[$key]['bill_sign'] = 5;
                            //更新可点击
                            $bills[$key]['button_sign'] = 2;
                            //金额
                            $bills[$key]['bill_money'] = '--';
                            //天数
                            $differ_day = ($strto_now - $strto_bank_bill_time) / 86400;
                            $bills[$key]['differ_day'] = ceil($differ_day) . '';

                        } else {
                            //几天出账、更新不可点击、--
                            $bills[$key]['bill_sign'] = 6;
                            //更新不可点击
                            $bills[$key]['button_sign'] = 3;
                            //金额
                            $bills[$key]['bill_money'] = '--';
                            //天数
                            $differ_day = ($strto_next_bill_time - $strto_now) / 86400;
                            $bills[$key]['differ_day'] = ceil($differ_day) . '';

                        }
                    }

                } elseif ($strto_now > $strto_repay_time && $strto_now < $strto_next_bill_time && $bill_status == 0) {
                    //不属于当前月
                    if ($billCount == 0) {
                        //逾期、更新可点击、1000
                        $bills[$key]['bill_sign'] = 1;
                        //设为已还
                        $bills[$key]['button_sign'] = 2;
                        //金额
                        $bills[$key]['bill_money'] = $value['bill_money'];
                        //天数
                        $differ_day = ($strto_now - $mysql_strto_repay_time) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';

                    } else {
                        //1.28<2.1<2.2 未还
                        //逾期、设为已还、1000
                        $bills[$key]['bill_sign'] = 1;
                        //设为已还
                        $bills[$key]['button_sign'] = 1;
                        //金额
                        $bills[$key]['bill_money'] = $value['bill_money'];
                        //天数
                        $differ_day = ($strto_now - $mysql_strto_repay_time) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';

                    }

                } elseif ($strto_now > $strto_repay_time && $strto_now < $strto_next_bill_time && $bill_status == 1) {

                    if ($billCount == 0) {
                        //不在账单周期范围内
                        //几天出账、更新可点击、--
                        $bills[$key]['bill_sign'] = 6;
                        //更新可点击
                        $bills[$key]['button_sign'] = 2;
                        //金额
                        $bills[$key]['bill_money'] = '--';
                        //天数
                        $differ_day = ($strto_next_bill_time - $strto_now) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';

                    } else {
                        //几天出账、更新不可点击、--
                        $bills[$key]['bill_sign'] = 6;
                        //更新不可点击
                        $bills[$key]['button_sign'] = 3;
                        //金额
                        $bills[$key]['bill_money'] = '--';
                        //天数
                        $differ_day = ($strto_next_bill_time - $strto_now) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';

                    }
                }

            }
            $bills[$key]['billCount'] = $billCount;
        }

        return $bills ? $bills : [];
    }

    /**
     * 按出账天数进行排序
     * @param array $params
     * @return array
     */
    public static function getAlreadyMultisort($params = [])
    {
        foreach ($params as $key => $val) {
            $volume[$key] = isset($val['differ_day']) ? $val['differ_day'] : '';
        }

        if ($params) {
            array_multisort($volume, SORT_ASC, $params);
        }

        return $params ? $params : [];
    }

    /**
     * 列表状态值
     * @param array $bills
     * @return array
     */
    public static function getBillsStatus($bills = [])
    {
        //billinfo_sign 当没有账单时，做默认判断；
        foreach ($bills as $key => $value) {
            //标识账单信息是否存在 0不存在，1存在
            $billinfo_sign = $value['billinfo_sign'];

            //账单日
            $bank_bill_date = $value['bank_bill_date'];
            //还款日
            $bank_repay_day = $value['bank_repay_day'];
            //当前日期
            $now = date('Y-m-d', time());
            $strto_now = strtotime($now);
            $now_bill_time = date('Y-m-' . $bank_bill_date, time());

            //当前时间下的账单日
            $bank_bill_time = date('Y-m-' . $bank_bill_date, time());
            $strto_bank_bill_time = strtotime($bank_bill_time);
            //当前时间下的下一个账单日
            $next_bill_time = date('Y-m-d', strtotime("$bank_bill_time +1 month"));
            $strto_next_bill_time = strtotime($next_bill_time);
            //当前时间下的还款日

            //还款日与账单日求差
            $int_bank_repay_day = intval($bank_repay_day);
            $int_bank_bill_date = intval($bank_bill_date);
            $differ = bcsub($int_bank_repay_day, $bank_bill_date);
            //1.账单日<还款日&&账单日还款日相差17天以上，还款日期在同一个月；
            if ($int_bank_repay_day > $int_bank_bill_date && $differ >= UserBillPlatformConstant::BILL_DIFFER_VALUE) {
                $repay_time = date('Y-m-' . $value['bank_repay_day'], time());
            } else {
                //2.不满17天或还款日小于账单日 还款日期在下个月
                $now_year_month = date('Y-m', time());
                $repay_time = date('Y-m-' . $value['bank_repay_day'], strtotime("$now_year_month +1 month"));
            }

            $now_repay_time = $repay_time;
            $strto_repay_time = strtotime($repay_time);

            //当前时间下的下一个还款日
            $next_repay_time = date('Y-m-d', strtotime("$repay_time +1 month"));

            if ($strto_now < strtotime($now_bill_time)) {
                //当前时间下的账单日-1
                $bank_bill_time = date('Y-m-d', strtotime("$bank_bill_time -1 month"));
                $strto_bank_bill_time = strtotime($bank_bill_time);
                //当前时间下的下一个账单日-1
                $next_bill_time = date('Y-m-' . $bank_bill_date, time());
                $strto_next_bill_time = strtotime($next_bill_time);
                //当前时间下的还款日-1
                $repay_time = date('Y-m-d', strtotime("$repay_time -1 month"));
                $strto_repay_time = strtotime($repay_time);
                //当前时间下的下一个还款日
                $next_repay_time = date('Y-m-d', strtotime("$repay_time +1 month"));
            }
            //SLogger::getStream()->info('repay_time-'.$key, ['data' => $repay_time]);
            //数据库账单的还款日
            $mysql_repay_time = $value['repay_time'];
            $mysql_strto_repay_time = strtotime($mysql_repay_time);
            //数据库的账单日
            $mysql_bill_time = $value['bank_bill_time'];
            $mysql_strto_bill_time = strtotime($mysql_bill_time);

            //状态
            $bill_status = $value['bill_status'];
            //导入状态
            $bank_is_import = $value['bank_is_import'];
            //账单总数量
            $billCount = $value['billCount'];
            //|button_sign |int   | 按钮 【1设为已还,2更新账单可点击,3更新账单不可点击】 |
            //|bill_sign |int   | 账单逾期状态 【1逾期 ，2几天到期 ，,3当天到期，4几天前出账，5当天出账，6几天出账】 |

            //会变数据
            //账单日、还款日 数据库不存在账单、显示当前月份账单日、还款日
            $bills[$key]['bank_bill_time'] = !empty($mysql_bill_time) ? $mysql_bill_time : $now_bill_time;
            $bills[$key]['repay_time'] = !empty($mysql_repay_time) ? $mysql_repay_time : $now_repay_time;
            $bills[$key]['bill_money'] = !empty($value['bill_money']) ? $value['bill_money'] : '--';
            $bills[$key]['button_sign'] = 0;
            $bills[$key]['bill_sign'] = 0;
            $bills[$key]['differ_day'] = 0;

            //手动输入 网贷 已还
            if ($bank_is_import == 0 && $bill_status == 1 && $billinfo_sign == 1) {
                $bills[$key]['bill_money'] = '--';

            } elseif ($bank_is_import == 0 && $bill_status != 1 && $billinfo_sign == 1) {
                //手动输入 网贷 当月都是几天到期，小于当月的都是逾期
                //设为已还
                $bills[$key]['button_sign'] = 1;
                //金额
                $bills[$key]['bill_money'] = $value['bill_money'];
                //天数
                $differ_day = ($strto_now - $mysql_strto_repay_time) / 86400;
                $bills[$key]['differ_day'] = ceil(abs($differ_day)) . '';

                if ($strto_now >= $mysql_strto_bill_time && $strto_now <= $mysql_strto_repay_time) {
                    //几天到期、设为已还、1000
                    $bills[$key]['bill_sign'] = 2;
                    if ($strto_now == $mysql_strto_repay_time) {
                        //当天到期、设为已还、1000
                        $bills[$key]['bill_sign'] = 3;

                    }
                } elseif ($strto_now > $mysql_strto_repay_time) {
                    //逾期、设为已还、1000
                    $bills[$key]['bill_sign'] = 1;

                }
                //魔蝎导入
            } elseif ($bank_is_import == 1 && $billinfo_sign == 1) {
                // 1.2<1.10<1.28 未还
                if ($strto_now >= $strto_bank_bill_time && $strto_now <= $strto_repay_time && $bill_status == 0) {
                    //不属于当前账单周期内
                    if ($billCount == 0) {
                        //逾期、更新可点、1000
                        $bills[$key]['bill_sign'] = 1;
                        //设为已还
                        $bills[$key]['button_sign'] = 2;
                        //金额
                        $bills[$key]['bill_money'] = $value['bill_money'];
                        //天数
                        $differ_day = ($strto_now - $mysql_strto_repay_time) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';
                        //账单日、还款日
                        $bills[$key]['bank_bill_time'] = $mysql_bill_time;;
                        $bills[$key]['repay_time'] = $mysql_repay_time;

                    } else {
                        if ($strto_now == $strto_repay_time && $bill_status == 0 && $billCount == 1) {
                            //当天到期、设为已还、1000
                            $bills[$key]['bill_sign'] = 3;
                            //更新可点击
                            $bills[$key]['button_sign'] = 1;
                            //金额
                            $bills[$key]['bill_money'] = $value['bill_money'];
                            //天数
                            $differ_day = ($strto_now - $strto_repay_time) / 86400;
                            $bills[$key]['differ_day'] = ceil($differ_day) . '';
                            //账单日、还款日
                            $bills[$key]['bank_bill_time'] = $mysql_bill_time;
                            $bills[$key]['repay_time'] = $mysql_repay_time;

                        } else {
                            //属于账单周期内
                            //几天到期、设为已还、1000
                            $bills[$key]['bill_sign'] = 2;
                            //设为已还
                            $bills[$key]['button_sign'] = 1;
                            //金额
                            $bills[$key]['bill_money'] = $value['bill_money'];
                            //天数
                            $differ_day = ($strto_repay_time - $strto_now) / 86400;
                            $bills[$key]['differ_day'] = ceil($differ_day) . '';
                            //账单日、还款日
                            $bills[$key]['bank_bill_time'] = $mysql_bill_time;
                            $bills[$key]['repay_time'] = $mysql_repay_time;
                        }
                    }

                } elseif ($strto_now >= $strto_bank_bill_time && $strto_now <= $strto_repay_time && $bill_status == 1) {
                    // 1.2<1.10<1.28 已还
                    //不属于当前账单周期范围内
                    if ($billCount == 0) {
                        //几天前出账、更新可点击、--
                        $bills[$key]['bill_sign'] = 4;
                        //更新可点击
                        $bills[$key]['button_sign'] = 2;
                        //金额
                        $bills[$key]['bill_money'] = '--';
                        //天数
                        $differ_day = ($strto_now - $strto_bank_bill_time) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';
                        //账单日、还款日
                        $bills[$key]['bank_bill_time'] = $bank_bill_time;
                        $bills[$key]['repay_time'] = $repay_time;

                    } else {
                        if ($strto_now == $strto_bank_bill_time && $bill_status == 1) {
                            //当天出账、更新可点击、--
                            $bills[$key]['bill_sign'] = 5;
                            //更新可点击
                            $bills[$key]['button_sign'] = 2;
                            //金额
                            $bills[$key]['bill_money'] = '--';
                            //天数
                            $differ_day = ($strto_now - $strto_bank_bill_time) / 86400;
                            $bills[$key]['differ_day'] = ceil($differ_day) . '';
                            //账单日、还款日
                            $bills[$key]['bank_bill_time'] = $bank_bill_time;
                            $bills[$key]['repay_time'] = $repay_time;

                        } else {
                            //几天出账、更新不可点击、--
                            $bills[$key]['bill_sign'] = 6;
                            //更新不可点击
                            $bills[$key]['button_sign'] = 3;
                            //金额
                            $bills[$key]['bill_money'] = '--';
                            //天数
                            $differ_day = ($strto_next_bill_time - $strto_now) / 86400;
                            $bills[$key]['differ_day'] = ceil($differ_day) . '';
                            //账单日、还款日
                            $bills[$key]['bank_bill_time'] = $next_bill_time;
                            $bills[$key]['repay_time'] = $next_repay_time;

                        }
                    }

                } elseif ($strto_now > $strto_repay_time && $strto_now < $strto_next_bill_time && $bill_status == 0) {
                    //不属于当前月
                    if ($billCount == 0) {
                        //逾期、更新可点击、1000
                        $bills[$key]['bill_sign'] = 1;
                        //设为已还
                        $bills[$key]['button_sign'] = 2;
                        //金额
                        $bills[$key]['bill_money'] = $value['bill_money'];
                        //天数
                        $differ_day = ($strto_now - $mysql_strto_repay_time) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';
                        //账单日、还款日
                        $bills[$key]['bank_bill_time'] = $mysql_bill_time;;
                        $bills[$key]['repay_time'] = $mysql_repay_time;

                    } else {
                        //1.28<2.1<2.2 未还
                        //逾期、设为已还、1000
                        $bills[$key]['bill_sign'] = 1;
                        //设为已还
                        $bills[$key]['button_sign'] = 1;
                        //金额
                        $bills[$key]['bill_money'] = $value['bill_money'];
                        //天数
                        $differ_day = ($strto_now - $mysql_strto_repay_time) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';
                        //账单日、还款日
                        $bills[$key]['bank_bill_time'] = $mysql_bill_time;
                        $bills[$key]['repay_time'] = $mysql_repay_time;

                    }

                } elseif ($strto_now > $strto_repay_time && $strto_now < $strto_next_bill_time && $bill_status == 1) {

                    if ($billCount == 0) {
                        //不在账单周期范围内
                        //几天出账、更新可点击、--
                        $bills[$key]['bill_sign'] = 6;
                        //更新可点击
                        $bills[$key]['button_sign'] = 2;
                        //金额
                        $bills[$key]['bill_money'] = '--';
                        //天数
                        $differ_day = ($strto_next_bill_time - $strto_now) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';
                        //账单日、还款日
                        $bills[$key]['bank_bill_time'] = $next_bill_time;
                        $bills[$key]['repay_time'] = $next_repay_time;

                    } else {
                        //几天出账、更新不可点击、--
                        $bills[$key]['bill_sign'] = 6;
                        //更新不可点击
                        $bills[$key]['button_sign'] = 3;
                        //金额
                        $bills[$key]['bill_money'] = '--';
                        //天数
                        $differ_day = ($strto_next_bill_time - $strto_now) / 86400;
                        $bills[$key]['differ_day'] = ceil($differ_day) . '';
                        //账单日、还款日
                        $bills[$key]['bank_bill_time'] = $next_bill_time;
                        $bills[$key]['repay_time'] = $next_repay_time;

                    }
                }

            }
            $bills[$key]['billCount'] = $billCount;
        }

        return $bills ? $bills : [];
    }


    /**
     * 首页列表数据处理
     * @param array $bills
     * @return array
     */
    public static function getHomeBills($bills = [], $data = [])
    {
        $datas = [];
        foreach ($bills as $key => $value) {

            $datas[$key]['id'] = $value['id'];
            $datas[$key]['bill_platform_id'] = $value['platformInfo']['id'];
            $datas[$key]['bank_short_name'] = $value['bank_short_name'];
            $datas[$key]['bank_logo'] = QiniuService::getImgs($value['bank_logo']);
            $datas[$key]['bank_watermark_link'] = QiniuService::getImgs($value['bank_watermark_link']);
            $datas[$key]['bank_credit_card_num'] = $value['platformInfo']['bank_credit_card_num'];
            $datas[$key]['bank_is_import'] = $value['platformInfo']['bank_is_import'];
            $datas[$key]['bill_platform_type'] = $value['platformInfo']['bill_platform_type'];
            $product_bill_period_num = $value['product_bill_period_num'];
            $product_period_total = $value['platformInfo']['product_period_total'];
            $datas[$key]['product_period'] = !empty($product_bill_period_num) ? $product_bill_period_num . '/' . $product_period_total : '';
            $datas[$key]['product_logo'] = QiniuService::getImgToBillProduct();
            $datas[$key]['product_name'] = $value['platformInfo']['product_name'];
            $datas[$key]['product_watermark_link'] = QiniuService::getWatermarkImgToBillProduct();

            //会变数据
            $datas[$key]['bank_bill_time'] = DateUtils::formatTimeToMd($value['bank_bill_time']);
            $datas[$key]['repay_time'] = DateUtils::formatTimeToMd($value['repay_time']);
            $datas[$key]['bill_money'] = $value['bill_money'];
            $datas[$key]['bill_type'] = $data['billType'];
            $datas[$key]['button_sign'] = isset($value['button_sign']) ? $value['button_sign'] : 0;
            $datas[$key]['bill_sign'] = isset($value['bill_sign']) ? $value['bill_sign'] : 0;
            $datas[$key]['differ_day'] = isset($value['differ_day']) ? $value['differ_day'] : 0;
            $datas[$key]['bill_type'] = isset($data['billType']) ? $data['billType'] : 0;
            $datas[$key]['bill_count'] = isset($value['billCount']) ? $value['billCount'] : 0;

        }

        return $datas ? $datas : [];
    }

    /**
     * 信用卡账单详情处理
     * @param array $params
     * @return array
     */
    public static function getCreditcardBillDetail($params = [])
    {
        //导入信用卡账单数据
        $detail = [];
        foreach ($params as $k => $item) {
            $detail[$k]['description'] = $item['description'];
            if ($item['amount_money'] && $item['amount_money'] < 0) {
                $detail[$k]['amount_money'] = $item['amount_money'] . '';
                $detail[$k]['amount_money_sign'] = 0;
            } else {
                $detail[$k]['amount_money'] = '+' . $item['amount_money'];
                $detail[$k]['amount_money_sign'] = 1;
            }

            $detail[$k]['trans_date'] = date('m/d', strtotime($item['trans_date']));
        }

        return $detail ? $detail : [];
    }

    /**
     * 魔蝎可导入邮箱数据处理
     * @param array $params
     * @return array
     */
    public static function getImportBillMails($params = [])
    {
        foreach ($params as $key => $val) {
            $params[$key]['image_link'] = QiniuService::getImgs($val['image_link']);
        }

        return $params ? $params : [];
    }

    /**
     * 负债分析饼状图
     * @param array $billStatistics
     * @return array
     */
    public static function getMonthBillStatistics($billStatistics = [])
    {
        $datas = [];
        //总负债值定义
        $total_debts = 0;
        //定义百分比 但有可能之和不为1
        $percent = 0;
        //内层累计常量定义
        $inner_value_total = 0;

        foreach ($billStatistics as $key => $item) {
            //总负债值
            $total_debts = $item['total_debts'];
            //$percent = $percent + $item['count_percent'];
            //超过总值的95%
            $value_other = bcmul($item['total_debts'], 0.95);

            if ($inner_value_total < $value_other && $inner_value_total != $item['total_debts']) {
                //内层累计计算
                $inner_value_total = $inner_value_total + $item['debts'];
                //信用卡
                if ($item['bill_platform_type'] == 1) {
                    $datas[$key]['name'] = $item['bank_sname'];
                    $datas[$key]['value'] = $item['debts'];
                } elseif ($item['bill_platform_type'] == 2) {
                    //网贷
                    $datas[$key]['name'] = $item['product_name'];
                    $datas[$key]['value'] = $item['debts'];
                }
            } elseif ($inner_value_total >= $value_other && $inner_value_total != $item['total_debts']) {
                $datas[$key]['name'] = '其它';
                $datas[$key]['value'] = $item['total_debts'] - $inner_value_total;
                $inner_value_total = $inner_value_total + $item['debts'];
                break;
            }

        }

        $res['total_debts'] = $total_debts;
        $res['list'] = $datas;

        return $res ? $res : [];
    }

    /**
     * 饼状图百分数
     * 颜色显示格式：
     * {
     * name: '人人贷',
     * value: 304,
     * itemStyle: {
     *      normal: {
     *          color: 'rgb(1,175,80)'
     *          }
     *      }
     * }
     * @param array $billStatistics
     * @return array
     */
    public static function getPercentBillStatistics($billStatistics = [])
    {
        $total_debts = DateUtils::formatDataToBillion($billStatistics['total_debts']);

        $colors = UserBillPlatformConstant::BILL_ANALYSIS_BG_COLORS;
        //变量定义
        $i = 0;
        foreach ($billStatistics['list'] as $key => $item) {
            $billStatistics['list'][$key]['value'] = floatval($item['value']);
            $billStatistics['list'][$key]['debts'] = bcadd($item['value'], 0, 2);
            $i = $i + 1;
            $i = $i <= count($colors) ? $i : 1;
            $billStatistics['list'][$key]['itemStyle']['normal']['color'] = isset($colors[$key]) ? $colors[$key] : $colors[$i];
        }

        $res['total_debts'] = $total_debts;
        $res['list'] = $billStatistics['list'];

        return $res ? $res : [];
    }

    /**
     * 梳理数据格式，将年份作为key值
     * @param $billStatistics
     * @return array
     */
    public static function getYearBillStatistics($billStatistics = [])
    {
        $datas = [];
        foreach ($billStatistics as $key => $item) {
            $datas[$item['bill_count_month']] = $item['total_debts'];
        }

        return $datas ? $datas : [];
    }

    /**
     * 保证12个月月份存在
     * @param $billStatistics
     * @param $regions
     * @return array
     */
    public static function getBillStatisticsYear($billStatistics, $regions)
    {
        $datas = [];
        foreach ($regions as $key => $val) {
            if (isset($billStatistics[$val])) {
                $datas[$key]['bill_count_month'] = $val;
                $datas[$key]['total_debts'] = $billStatistics[$val];
            } else {
                $datas[$key]['bill_count_month'] = $val;
                $datas[$key]['total_debts'] = 0;
            }
        }

        return $datas ? $datas : [];
    }

    /**
     * 折线图数据格式处理
     * @param array $billStatistics
     * @return array
     */
    public static function getLineChartBillData($billStatistics = [])
    {
        //当前年
        $year = date('Y', time());
        $datas = [];
        foreach ($billStatistics as $key => $item) {
            //当前年 展示03月；不是当前年 展示2017年03月
            $string = strtotime($item['bill_count_month']);
            $billYear = date('Y', $string);
            if ($year == $billYear) {
                $datas['bill_count_month'][] = DateUtils::formatTimeToMonthBym($item['bill_count_month']);
            } else {
                $datas['bill_count_month'][] = DateUtils::formatTimeToYmBySpot($item['bill_count_month']);
            }
            $datas['total_debts'][] = floatval($item['total_debts']);
        }

        return $datas ? $datas : [];
    }
}