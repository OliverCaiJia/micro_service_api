<?php

namespace App\Constants;

/**
 * Class BankcardConstant
 * @package App\Constants
 * 用户银行卡常量
 */
class UserBankcardConstant extends AppConstant
{
    //储蓄卡
    const CARD_TYPE_SAVING_CARD = 1;
    //信用卡
    const CARD_TYPE_CREDIT_CARD = 2;

    //信用卡 —— 支持银行及限额
    const QUOTA_BANKS_CREDIT_CARD = [
        [
            //银行名称
            'bankname' => '工商银行',
            //单笔订单
            'single_quota' => '5万',
            //单日订单
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '中国银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '建设银行',
            'single_quota' => '1万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '邮政储蓄',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '中信银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '光大银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '华夏银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
//        [
//            'bankname' => '招商银行',
//            'single_quota' => '5万',
//            'oneday_quote' => '5万',
//        ],
        [
            'bankname' => '兴业银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '浦发银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '平安银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '广发银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '北京银行',
            'single_quota' => '2万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '上海银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '民生银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '农业银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],

    ];

    //储蓄卡 —— 支持银行及限额
    const QUOTA_BANKS_SAVING_CARD = [
        [
            //银行名称
            'bankname' => '工商银行',
            //单笔订单
            'single_quota' => '5万',
            //单日订单
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '中国银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '建设银行',
            'single_quota' => '1万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '中信银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '光大银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '华夏银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '兴业银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '浦发银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '平安银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '广发银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '民生银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
        [
            'bankname' => '农业银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],
//        [
//            'bankname' => '交通银行',
//            'single_quota' => '5万',
//            'oneday_quote' => '5万',
//        ],
        [
            'bankname' => '广州银行',
            'single_quota' => '5万',
            'oneday_quote' => '5万',
        ],

    ];

}