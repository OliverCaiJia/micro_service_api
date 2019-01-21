<?php

namespace App\Helpers;


class DateUtils
{
    public static function toInt($obj)
    {
        return is_numeric($obj) ? intval($obj) : 0;
    }

    //转时间   今天 昨天 2016-8-3
    public static function formatToDay($param)
    {
        $time = strtotime(date('Y-m-d', time()));
        $create_time = explode(' ', $param);
        $createTimeStr = strtotime($param);
        if ($time - $createTimeStr == 0) {
            $createTime = '今天';
        } else if ($time - $createTimeStr == 86400) {
            $createTime = '昨天';
        } else {
            $createTime = $create_time[0];
        }
        return $createTime;

    }

    //转时间  2016-12-25
    public static function formatDate($string = '')
    {
        if (empty($string)) {
            return '';
        }

        $formatDate = explode(' ', $string);
        return $formatDate[0];
    }

    //转时间  2016/12/25 14:58
    public static function formatDateToMin($string = '')
    {
        $strtotime = strtotime($string);
        return date('Y/m/d H:i', $strtotime);
    }

    //转时间 2016-12-25 14:58
    public static function formatDateToYmdhi($string = '')
    {
        $strtotime = strtotime($string);
        return date('Y-m-d H:i', $strtotime);
    }

    //转时间  2017/08/12
    public static function formatDateToLeftdata($string = '')
    {
        $strtotime = strtotime($string);
        return date('Y/m/d', $strtotime);
    }

    /**
     * @param string $string
     * @return false|string
     * 转时间   2-12 12:23
     */
    public static function formatDateToMdhi($string = '')
    {
        $strtotime = strtotime($string);
        return date('n-j H:i', $strtotime);
    }

    /**
     * @param string $string
     * @return false|string
     * 转化日期为 01月02日格式
     */
    public static function formatDataToDay($string = '')
    {
        $string = strtotime($string);
        $formatData = date('m', $string) . '月' . date('d', $string) . '日';
        return $formatData ? $formatData : '';
    }

    /**转化日期为 1997年01月01日格式
     * @param string $string
     * @return string
     */
    public static function formatTimeToYmd($string = '')
    {
        $string = strtotime($string);
        $formatData = date('Y', $string) . '年' . date('m', $string) . '月' . date('d', $string) . '日';
        return $formatData ? $formatData : '';
    }

    /**
     * 转换日期 1970年01月
     * @param string $string
     * @return string
     */
    public static function formatTimeToYm($string = '')
    {
        $string = strtotime($string);
        $formatData = date('Y', $string) . '年' . date('m', $string) . '月';
        return $formatData ? $formatData : '';
    }

    /**
     * 转换日期 01.02
     * @param string $string
     * @return string
     */
    public static function formatTimeToMd($string = '')
    {
        $string = strtotime($string);
        $formatData = date('m', $string) . '.' . date('d', $string);
        return $formatData ? $formatData : '';
    }

    /**
     * 转化时间格式 2017.01
     * @param string $string
     * @return string
     */
    public static function formatTimeToYmBySpot($string = '')
    {
        $string = strtotime($string);
        $formatData = date('Y', $string) . '.' . date('m', $string);
        return $formatData ? $formatData : '';
    }

    /**
     * 将时间转化为月  2017-01-01 转化为01
     * @param string $string
     * @return false|string
     */
    public static function formatTimeToMonthBym($string = '')
    {
        $string = strtotime($string);
        $formatData = date('n', $string);
        return $formatData ? $formatData . '月' : '';
    }

    /**
     * 转换日期 1997.01.02
     * @param string $string
     * @return string
     */
    public static function formatTimeToYmdBySpot($string = '')
    {
        $string = strtotime($string);
        $formatData = date('Y', $string) . '.' . date('m', $string) . '.' . date('d', $string);
        return $formatData ? $formatData : '';
    }

    /**
     * 转化为亿 保留两位小数直接舍去
     * @param string $money
     * @return string
     */
    public static function formatDataToBillion($money = '')
    {
        //将116501203.20，显示为1.16亿
        if ($money >= 100000000) {
            $money = bcdiv($money, 100000000, 2);
            return $money . '亿';
        } else {
            return $money . '';
        }
    }

    /**
     * @param $money
     * @return string
     * 万为单位的转换
     */
    public static function formatMoney($money)
    {
        //贷款成功数
        if ($money >= 10000) {
            return sprintf("%.1f", $money / 10000) . '万';
        } else {
            return $money . '';
        }
    }

    /**
     * @param $money
     * @return string
     * 万为单位的转换——向上取整
     */
    public static function ceilMoney($money)
    {
        //将10001 转换成 1.1万
        if ($money >= 10000) {
            $money = ceil($money / 1000);
            return sprintf("%.1f", $money / 10) . '万';
        } else {
            return $money . '';
        }
    }

    /**
     * @param $money
     * @return string
     * 万为单位的转换  取整 带单位 大于10万
     */
    public static function formatMoneyToInt($money)
    {
        if ($money >= 100000) {
            return sprintf("%.0f", $money / 10000) . '万元';
        } else {
            return $money . '元';
        }
    }

    /**
     * @param $money
     * @return string
     * 万为单位的转换  取整 带单位
     */
    public static function formatMoneyToStr($money)
    {
        if ($money >= 10000) {
            return sprintf("%.0f", $money / 10000) . '万元';
        } else {
            return $money . '';
        }
    }

    /**
     * @param $money
     * @return string
     * 万为单位的转换  取整 带单位 大于1万
     */
    public static function formatNumToThousand($money)
    {
        if ($money >= 10000) {
            return round($money / 10000, 1) . '万元';
        } else {
            return $money . '元';
        }
    }

    /**
     * @param $time
     * @return string
     * 转化为年，月
     */
    public static function formatTimeToYear($time)
    {
        if ($time >= 30 && $time < 360) {
            return sprintf("%.0f", $time / 30) . '个月';
        } elseif ($time >= 360) {
            return sprintf("%.0f", $time / 360) . '年';
        } else {
            return $time . '天';
        }
    }

    /**
     * 转化为 日/月
     * @param $time
     * @return string
     */
    public static function formatTimeToMonth($time)
    {
        if ($time >= 30) {
            return sprintf("%.0f", $time / 30) . '个月';
        } else {
            return $time . '天';
        }
    }

    /**
     * @param $param
     * @return string
     * 四舍五入  保留两位小数
     */
    public static function formatRound($param)
    {
        if ($param >= 10000) {
            $param = round($param / 10000, 1);
            return $param . '万人';
        } else {
            return $param . '人';
        }
    }

    /**
     * @param $param
     * @return string
     * 四舍五入转化为万
     */
    public static function formatMathToThous($param)
    {
        if ($param >= 10000) {
            return round($param / 10000) . '万';
        } else {
            return $param . '';
        }
    }

    /**
     * 四舍五入化为元  过万不带单位  不过万带单位
     * @param $param
     * @return string
     */
    public static function formatIntToThousBandunit($param)
    {
        if ($param >= 10000) {
            return ($param / 10000) . '万';
        } else {
            return $param . '元';
        }
    }

    /**
     * 最后结果 1.5万/2万
     * @param $param
     * @return string
     */
    public static function formatIntToThous($param)
    {
        if ($param >= 10000) {
            return ($param / 10000) . '万';
        } else {
            return $param . '';
        }
    }

    /**
     * 格式化金额 大于1000转化为万 两位小数直接舍去
     * 0.10万
     * @param $param
     * @return string
     */
    public static function formatIntToThou($param)
    {
        if ($param >= 1000) {
            return bcdiv($param, 10000, 2) . '万';
        } else {
            return $param . '';
        }
    }


    /*
    * @desc    分页
    * @param   array   $data   所有数据
    * @param   num     $page   每页显示条数
    * @desc    num     $num    页码
    * */
    public static function pageInfo($data, $page = 1, $num = 5)
    {
        //总条数
        $total = count($data);
        //总页数
        $pageTotal = ceil($total / $num);
        //偏移量
        $offset = ($page - 1) * $num;
        //分页显示
        $new_data = [];
        $total_num = $num * $page;
        for ($i = $offset; $i < $total_num; $i++) {
            if (isset($data[$i])) {
                $new_data[] = $data[$i];
            }
        }
        $newData['list'] = $new_data;
        $newData['pageCount'] = $pageTotal;

        return $newData;
    }

    /**
     * @param array $data
     * 过滤空数组，重新整合key值
     */
    public static function formatArray($data = [])
    {
        $data = array_filter($data);
        $data = array_values($data);
        return $data ? $data : [];
    }

    /**
     * 转化为百分数 1-5 转化为 百分比  先除以5再乘以100
     * 向上取整
     * @param $data
     * @return string
     */
    public static function formatPercentage($data)
    {
        return ceil($data * 20) . '%';
    }

    /**
     * 数据个数处理  保留两位小数 10.01为10.01，10.00为10
     * @param string $param
     * @return int|string
     */
    public static function formatData($param = '')
    {
        $res = explode('.', $param);
        if (isset($res[1]) && $res[1] == 00) {
            $param = intval($param);
        }
        return $param;
    }

    /**
     * 验证颜色值 2273B9
     * @param string $color
     * @return string
     */
    public static function checkLengthColor($color = '')
    {
        $color = trim($color);
        if (strlen($color) == 6) {
            return $color;
        }

        return '';
    }

    /**
     * 格式化生日　　1990-2-5 => 1990-02-05
     * @param string $birthday
     * @return false|string
     */
    public static function formatBirthdayByStrto($birthday = '')
    {
        if (empty($birthday)) return '';
        $birthday = strtotime($birthday);

        $newBirthday = date('Y-m-d', $birthday);

        return $newBirthday ? $newBirthday : '';
    }

    /**
     * 格式化出生年月 19900101 => 1990-01-01
     * @param string $birthday
     * @return string
     */
    public static function formatBirthdayToYmd($birthday = '')
    {
        if (empty($birthday)) return '';
        $newBirthday = mb_substr($birthday, 0, 4) . '-' . mb_substr($birthday, 4, 2) . '-' . mb_substr($birthday, -2);

        return $newBirthday ? $newBirthday : '';
    }

    /**
     * 获取生日  1992-02-02 00:00:00 => 1992-02-02
     * @param string $birthday
     * @return string
     */
    public static function getBirthday($birthday = '')
    {
        if (empty($birthday)) return '';
        $birthdays = explode(' ', $birthday);

        return $birthdays[0];
    }
}
