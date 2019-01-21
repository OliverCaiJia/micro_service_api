<?php
namespace App\Strategies;

use App\Helpers\DateUtils;

/**
 * Class UserProfileStrategy
 * @package App\Strategies
 * 信用信息
 */
class UserProfileStrategy extends AppStrategy
{

    /**
     * @param null $data
     * @return string
     * @desc    年龄
     */

    public static function ageToInt($data = '')
    {
        $i =  DateUtils::toInt($data);
        if($i>0 && $i<=17 ) return 1;
        elseif ($i>=18 && $i<=24 ) return 2;
        elseif ($i>=25 && $i<=30) return 3;
        elseif ($i>=30) return 4;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    地址类型
     */
    public static function addintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if($i == 1)  return '租住';
        elseif ($i == 2 ) return '单位/学校宿舍';
        elseif ($i == 3 ) return '亲属房产';
        elseif ($i == 4 ) return '自有房产';
        else return '';
    }

    public static function addstrToInt($data = '')
    {
        $str = trim($data);
        if($str == '租住') return 1;
        elseif ($str == '单位/学校宿舍') return 2;
        elseif ($str == '亲属房产') return 3;
        elseif ($str == '自有房产') return 4;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    婚姻状况
     */
    public static function marintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if($i == 1)  return '未婚';
        elseif ($i == 2 ) return '已婚';
        else return '';
    }

    public static function marstrToInt($data = '')
    {
        $str = trim($data);
        if($str == '未婚') return 1;
        elseif ($str == '已婚') return 2;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    有无房产
     */
    public static function prointToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if($i == 1)  return '无';
        elseif ($i == 2 ) return '有房有按揭';
        elseif ($i == 3 ) return '有房无按揭';
        else return '';
    }

    public static function prostrToInt($data = '')
    {
        $str = trim($data);
        if($str == '无') return 1;
        elseif ($str == '有房有按揭') return 2;
        elseif ($str == '有房无按揭') return 3;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    有无车产
     */
    public static function carintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if($i == 1)  return '无';
        elseif ($i == 2 ) return '有车有按揭';
        elseif ($i == 3 ) return '有车无按揭';
        else return '';
    }

    public static function carstrToInt($data = '')
    {
        $str = trim($data);
        if($str == '无') return 1;
        elseif ($str == '有车有按揭') return 2;
        elseif ($str == '有车无按揭') return 3;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    紧急联系人关系
     */
    public static function emeintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1 ) return '朋友/同学/同事';
        elseif ($i == 2 ) return '非直系亲属';
        elseif ($i == 3 ) return '直系亲属';
        else return '';
    }

    public static function emestrToInt($data = '')
    {
        $str = trim($data);
        if ($str == '朋友/同学/同事') return 1;
        elseif ($str == '非直系亲属') return 2;
        elseif ($str == '直系亲属') return 3;
        else return 0;
    }

}