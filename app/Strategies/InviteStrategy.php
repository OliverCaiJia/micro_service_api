<?php

namespace App\Strategies;

/**
 * @author zhaoqiying
 */
use App\Constants\CreditConstant;
use App\Constants\InviteConstant;
use App\Helpers\DateUtils;
use App\Helpers\Formater\NumberFormater;
use App\Helpers\LinkUtils;
use App\Helpers\RestUtils;
use App\Models\Factory\InviteFactory;
use App\Strategies\AppStrategy;

/**
 * 身份策略
 *
 * Class UserStrategy
 * @package App\Strategies
 */
class InviteStrategy extends AppStrategy
{
    /**
     * @return string
     * 邀请码
     */
    public static function createCode()
    {
        $code = date('y') . date('m') .  date('d') . UserStrategy::getRandChar(6, 'NC');
        return $code;
    }

    /**
     * @param $invite
     * @return mixed
     * 邀请好友
     */
    public static function toInviteSign($invite_num, $invite_code)
    {
        if ($invite_num >= 1 && $invite_num < 2) {
            $datas['firstSign']  = CreditConstant::SIGN_FULL;
            $datas['secondSign'] = CreditConstant::DEFAULT_EMPTY;
            $datas['thirdSign']  = CreditConstant::DEFAULT_EMPTY;
        } elseif ($invite_num >= 2 && $invite_num < 3) {
            $datas['firstSign']  = CreditConstant::SIGN_FULL;
            $datas['secondSign'] = CreditConstant::SIGN_FULL;
            $datas['thirdSign']  = CreditConstant::DEFAULT_EMPTY;
        } elseif ($invite_num >= 3) {
            $datas['firstSign']  = CreditConstant::SIGN_FULL;
            $datas['secondSign'] = CreditConstant::SIGN_FULL;
            $datas['thirdSign']  = CreditConstant::SIGN_FULL;
        } else {
            $datas['firstSign']  = CreditConstant::DEFAULT_EMPTY;
            $datas['secondSign'] = CreditConstant::DEFAULT_EMPTY;
            $datas['thirdSign']  = CreditConstant::DEFAULT_EMPTY;
        }
        $inviteArr[0]['invite_score'] = 250;
        $inviteArr[0]['invite_sign']  = $datas['firstSign'];
        $inviteArr[0]['invite_url']   = LinkUtils::shareLanding($invite_code);
        $inviteArr[1]['invite_score'] = 300;
        $inviteArr[1]['invite_sign']  = $datas['secondSign'];
        $inviteArr[1]['invite_url']   = LinkUtils::shareLanding($invite_code);
        $inviteArr[2]['invite_score'] = 450;
        $inviteArr[2]['invite_sign']  = $datas['thirdSign'];
        $inviteArr[2]['invite_url']   = LinkUtils::shareLanding($invite_code);
        return $inviteArr;
    }

    /**
     * @param array $logArr
     * 邀请流水处理状态
     */
    public static function toStatusStr($logArrs = [])
    {
        $logArr = $logArrs['list'];
        foreach ($logArr as $key => $val) {
            $logArr[$key]['status'] = self::statusStr($val['status']);
            if ($val['status'] == 3) {
                $logArr[$key]['invite_money'] = NumberFormater::formatMoney(InviteConstant::APPLY_MONEY);
            } else {
                $logArr[$key]['invite_money'] = '0.00';
            }
        }
        $logLists['list'] = $logArr ? $logArr : [];
        $logLists['pageCount'] = $logArrs['pageCount'];
        return $logLists;
    }

    /**
     * @param string $int
     * 邀请流水状态  1邀请中，2已注册，3已申请
     */
    public static function statusStr($int = '')
    {
        $i = DateUtils::toInt($int);
        if ($i == 1) return '邀请中';
        elseif ($i == 2) return '已注册';
        elseif ($i == 3) return '已申请';
        else return '邀请中';
    }


}
