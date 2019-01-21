<?php

namespace App\Helpers;

use App\Services\AppService;

class LinkUtils
{

    public static function getRand()
    {
        $rand = mt_rand(500000, 1200000);
        return $rand;
    }

    // 资讯链接
    public static function appLink($id)
    {
        return AppService::M_URL . '/html/consultApp2.2.html?newsId=' . $id;
    }

    // 产品分享
    public static function productShare($id)
    {
        return AppService::M_URL . '/html/product_result.html?productId=' . $id;
    }

    // 第三版 2.9.0 产品分享
    public static function thirdEditionProductShare($id)
    {
        return AppService::M_URL . '/html/product_result_fixes.html?productId=' . $id;
    }

    //分享落地页
    public static function shareLanding($invite_code = '')
    {
        return AppService::EVENT_URL . '/m/landing/index.html?sd_invite_code=' . $invite_code;
    }

    // 分享加积分
    public static function shareAddScores($userId = 0)
    {
        return AppService::M_URL . '/html/mine_aboutour.html?userId=' . $userId;
    }

    // 积分页面单独分享
    public static function shareOnlyLink($userId = 0)
    {
        return AppService::EVENT_URL . '/m/landing/index.html?userId=' . $userId . '&winzoom=1';
    }

    // 关于我们
    public static function shareOur()
    {
        return AppService::M_URL . '/html/mine_aboutour.html';
    }

    //关于现金提现规则说明
    public static function getAccountRule()
    {
        return AppService::M_URL . '/html/mine_cashRule.html';
    }

    //android 调 h5 的帮助中心连接地址
    public static function getHelpsToAndroid()
    {
        return AppService::M_URL . '/html/mine_help.html';
    }

    //商务合作
    public static function BusinessCooperation()
    {
        return AppService::M_URL . '/html/cooperation.html';
    }

    //身份证认证——用户协议
    public static function getAgreement()
    {
        return AppService::M_URL . '/html/agreement.html';
    }

    //信用卡 —— 支持银行列表
    public static function quotaCreditCardBankLink()
    {
        return AppService::M_URL . '/html/support_credit_card.html';
    }

    //储蓄卡 —— 支持银行列表
    public static function quotaSavingCardBankLink()
    {
        return AppService::M_URL . '/html/support_bank.html';
    }

    //个人报告查询授权书
    public static function getAuthorization()
    {
        return AppService::M_URL . '';
    }

    //报告样本
    public static function getSample()
    {
        return AppService::M_URL . '';
    }
}
