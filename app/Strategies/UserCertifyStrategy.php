<?php
namespace App\Strategies;
use App\Helpers\DateUtils;


/**
 * Class CertifyStrategy
 * @package App\Strategies
 * 审核资料策略层
 */
class UserCertifyStrategy extends AppStrategy
{
    /**
     * @param null $data
     * @return string
     * @desc    芝麻分
     */
    public static function zhiintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if($i == 1)  return '350~600';
        elseif ($i == 2 ) return '601~700';
        elseif ($i == 3 ) return '701以上';
        elseif ($i == 4 ) return '无';
        else return '';
    }

    public static function zhistrToInt($data = '')
    {
        $str = trim($data);
        if($str == '350~600') return 1;
        elseif ($str == '601~700') return 2;
        elseif ($str == '701以上') return 3;
        elseif ($str == '无') return 4;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    淘宝、京东、人行征、学信网账号
     */
    public static function twointToStr($data = '')
    {
        $i = DateUtils::toInt($data);
        if($i == 1)  return '无';
        elseif ($i == 2 ) return '可登录';
        else return '';
    }

    public static function twostrToInt($data = '')
    {
        $str = trim($data);
        if($str == '无') return 1;
        elseif ($str == '可登录') return 2;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    信用卡
     */
    public static function creditintToStr($data = '')
    {
        $i = DateUtils::toInt($data);
        if($i == 1)  return '无';
        elseif ($i == 2 ) return '有';
        else return '';
    }

    public static function creditstrToInt($data = '')
    {
        $str = trim($data);
        if($str == '无') return 1;
        elseif ($str == '有') return 2;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    社保、公积金
     */
    public static function creintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if($i == 1)  return '无';
        elseif ($i == 2 ) return '连续6个月无断续';
        elseif ($i == 3 ) return '连续6个月无断续且为同一单位';
        elseif ($i == 4 ) return '连续12个月无断续';
        elseif ($i == 5 ) return '连续12个月无断续且为同一单位';
        else return '';
    }

    public static function crestrToInt($data = '')
    {
        $str = trim($data);
        if($str == '无') return 1;
        elseif ($str == '连续6个月无断续') return 2;
        elseif ($str == '连续6个月无断续且为同一单位') return 3;
        elseif ($str == '连续12个月无断续') return 4;
        elseif ($str == '连续12个月无断续且为同一单位') return 5;
        else return 0;
    }

    /**
     * @param null $data
     * @return string
     * @desc    银行信用贷款
     */
    public static function bankintToStr($data = null)
    {
        $i = DateUtils::toInt($data);
        if($i == 1)  return '无';
        elseif ($i == 2 ) return '有';
        else return '';
    }

    public static function bankstrToInt($data = '')
    {
        $str = trim($data);
        if($str == '无') return 1;
        elseif ($str == '有') return 2;
        else return 0;
    }


}