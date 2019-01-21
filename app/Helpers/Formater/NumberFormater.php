<?php

namespace App\Helpers\Formater;

class NumberFormater
{

    // 金额加单位
    public static function amountUnit($money = 0)
    {
        return $money . '元';
    }

    //保留两位小数点
    public static function roundedAmount($money = 0, $lot = 2)
    {
        return number_format($money, $lot, '.', '');
    }

    // 千分位格式金额
    public static function formatAmount($money = 0, $lot = 2)
    {
        return number_format($money, $lot, '.', ',');
    }

    // 电话号码  135****2356
    public static function processPhoneNum($phone = 0, $lot = 3)
    {
        return !empty($phone) ? substr($phone, 0, $lot) . '****' . substr($phone, -($lot + 1), $lot + 1) : null;
    }

    // 银行卡号
    public static function processBankNum($banknum = 0, $lot = 4)
    {
        return !empty($banknum) ? substr($banknum, 0, $lot) . '****' . substr($banknum, -$lot, $lot) : null;
    }

    //格式金额（万以下，显示数字千分位隔开保留2位小数点。万以上显示xx万，并保留2为小数点）
    public static function simplifyAmount($money = '', $lot = 2)
    {
        $intMoney = intval(str_replace(',', '', $money));
        if ($intMoney < 10000) {
            return self::formatAmount($money, $lot);
        }
        return number_format($money / 10000, $lot, '.', '') . '万';
    }

    //格式金额（万以下，显示数字千分位隔开保留2位小数点。万以上显示xx万，并保留2为小数点）
    public static function spAmount($money = '', $lot = 2)
    {
        $intMoney = intval(str_replace(',', '', $money));
        if ($intMoney < 10000) {
            return self::formatAmount($money, $lot);
        }
        return floor(($money * 10) / 10000) / 10 . '万';
    }

    // 处理日期
    public static function simplifyDateTime($date = '', $type = 'month')
    {
        return ($type == 'month') ? $date . '个月' : $date . '天';
    }

    // 生成百分比
    public static function beautifyPercent($mumbers = '', $lot = 2)
    {
        return number_format($mumbers, $lot, '.', '') . '%';
    }

    // 取几位小数
    public static function beautifyFloat($mumbers = '', $lot = 1)
    {
        $num = pow(10, $lot);
        return floor($mumbers * $num) / $num;
    }

    // 转小数点 多维数组
    public static function numberFormat($data = array(), $lot = 2)
    {
        $vessel = array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $vessel[$key] = self::numberFormat($value);
            } else {
                $vessel[$key] = number_format($value, $lot, '.', '');
            }
        }
        return $vessel;
    }

    //转时间
    public static function formatDate($string = '')
    {
        if (empty($string)) {
            return '0';
        }
        return date('Y-m-d', $string);
    }

    //转时间
    public static function formatDateTime($string = '')
    {
        if (empty($string)) {
            return '0';
        }
        return date('Y-m-d H:i', $string);
    }

    //转时间
    public static function formatDateSecondTime($string = '')
    {
        if (empty($string)) {
            return '0';
        }
        return date('Y-m-d H:i:s', $string);
    }

    //加法
    public static function add($mixe)
    {
        if (!is_array($mixe)) {
            return '必须传递数组';
        }
        return self::roundedAmount(array_sum($mixe));
    }

    //循环加法
    public static function arrAdd($arr)
    {
        if (!is_array($arr) || empty($arr)) {
            return 0;
        }
        $num = 0;
        foreach ($arr as $v) {
            $num += $v['receivingPrincipalInterest'];
        }
        return self::roundedAmount($num);
    }

    //排序
    public static function bubbleSort($arr, $key)
    {
        // 获得数组总长度
        $num = count($arr);
        // 正向遍历数组
        for ($i = 1; $i < $num; $i++) {
            // 反向遍历
            for ($j = $num - 1; $j >= $i; $j--) {
                // 相邻两个数比较
                if ($arr[$j][$key] < $arr[$j - 1][$key]) {
                    // 暂存较小的数
                    $iTemp = $arr[$j - 1];
                    // 把较大的放前面
                    $arr[$j - 1] = $arr[$j];
                    // 较小的放后面
                    $arr[$j] = $iTemp;
                }
            }
        }
        return $arr;
    }

    // 金额加 + -等
    public static function amountUnitPlus($money, $type = '+')
    {
        return $type . $money;
    }

    // 转金额  23.00
    public static function formatMoney($money = '')
    {
        return number_format($money,2);
    }

}
