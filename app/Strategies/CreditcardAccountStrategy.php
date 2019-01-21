<?php

namespace App\Strategies;

use App\Helpers\DateUtils;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Strategies\AppStrategy;

/**
 * Class CreditcardAccountStrategy
 * @package App\Strategies
 * 信用卡账户策略
 */
class CreditcardAccountStrategy extends AppStrategy
{
    /**
     * @param $account
     * @return mixed
     * 额度转为空
     */
    public static function getBeforeAccount($account)
    {
        $account['repay_amount'] = empty(intval($account['repay_amount'])) ? '' : $account['repay_amount'];
        return $account;
    }

    /**
     * @param $accounts
     * @return mixed
     * 额度转为空
     */
    public static function getRepayAccount($accounts)
    {
        foreach ($accounts as $key => $val) {
            $accounts[$key]['repay_amount'] = empty(intval($val['repay_amount'])) ? '' : $val['repay_amount'];
        }

        return $accounts;
    }

    /**
     * @param $params
     * @return string
     * 提醒日期
     */
    public static function getAlertTime($params)
    {
        $day = sprintf('%02d', $params['repay_day']);
        $year = substr($params['billTime'], 0, 4);
        $month = substr($params['billTime'], -2, 2);

        return $time = $year . '-' . $month . '-' . $day;
    }


    /**
     * @param $params
     * @return mixed
     * 转化时间  08/10
     */
    public static function getAccountbills($params)
    {
        foreach ($params as $key => $val) {
            if ($val['bills']) {
                $params[$key]['bills']['bill_time'] = DateUtils::formatDateToLeftdata($val['bills']['bill_time']);
            } else {
                $params[$key]['bills'] = RestUtils::getStdObj();
            }
            foreach ($val['billeds'] as $k => $v) {
                $params[$key]['billeds'][$k]['bill_time'] = DateUtils::formatDateToLeftdata($v['bill_time']);
            }
        }

        return $params;
    }

    /**
     * @param $params
     * @return mixed
     * 将年限相同的数据放到一个数组中
     */
    public static function getAccountbillsByYear($params)
    {
        foreach ($params as $key => $val) {
            foreach ($val['billeds'] as $k => $v) {
                $params[$key]['billeds'][$k]['bill_time'] = DateUtils::formatDateToLeftdata($v['bill_time']);
            }
        }
        return $params;
    }

    /**
     * @param $params
     * @return mixed
     * 按照年份从大到小进行排序
     */
    public static function getAccountbillsOrderByYear($params)
    {
        foreach ($params as $key => $val) {
            $params[$key]['billeds'] = array_values($params[$key]['billeds']);
            ksort($params[$key]['billeds']);
        }
        return $params;
    }

    /**
     * @param $params
     * @return mixed
     * 已还账单时间转化
     */
    public static function getBills($params)
    {
        foreach ($params as $key => $val) {
            $year = !empty($val['bill_time']) ? Utils::formatDateToYear($val['bill_time']) : '';
            $params[$key]['bill_time'] = !empty($val['bill_time']) ? Utils::formatDateToMonthDay($val['bill_time']) : '';
            $params[$key]['year'] = $year;
        }

        return $params;
    }

    /**
     * @param $params
     * @return mixed
     * 将相同年限的数据放到同个一个数组中
     */
    public static function getBillsByYear($params)
    {
        foreach ($params as $key => $val) {
            $datas[$val['year']]['year'] = $val['year'];
            $datas[$val['year']]['list'][] = $val;
        }
        $params = !empty($datas) ? $datas : [];
        return $params;
    }
}