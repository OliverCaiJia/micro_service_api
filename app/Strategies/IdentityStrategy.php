<?php

namespace App\Strategies;

/**
 * @author zhaoqiying
 */
use App\Constants\CreditConstant;
use App\Strategies\AppStrategy;

/**
 * 身份策略
 *
 * Class UserStrategy
 * @package App\Strategies
 */
class IdentityStrategy extends AppStrategy
{

    /**
     * @param $indent
     * @param $progress
     * @return int
     * 完善个人信息  所有
     */
    public static function toInfoSign($indent, $progress)
    {
        if ($indent == 1 && $progress == 21)
        {
            $info_sign = CreditConstant::SIGN_FULL;
        }
        elseif ($indent == 2 && $progress == 25)
        {
            $info_sign = CreditConstant::SIGN_FULL;
        }
        elseif ($indent == 3 && $progress == 25)
        {
            $info_sign = CreditConstant::SIGN_FULL;
        }
        elseif ($indent == 4 && $progress == 19)
        {
            $info_sign = CreditConstant::SIGN_FULL;
        }
        else
        {
            $info_sign = CreditConstant::DEFAULT_EMPTY;
        }
        return $info_sign;
    }

    /**
     * @param $progress
     * 基础信息完善
     */
    public static function toBasicSign($progress)
    {
        if ($progress == 7)
        {
            $info_sign = CreditConstant::SIGN_FULL;
        }
        else
        {
            $info_sign = CreditConstant::DEFAULT_EMPTY;
        }
        return $info_sign;
    }

}
