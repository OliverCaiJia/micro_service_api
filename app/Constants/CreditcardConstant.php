<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 信用卡模块常量
 */
class CreditcardConstant extends AppConstant
{
    //信用卡筛选等级
    const SELECT_DEGREE = [
        ['type_nid' => 0, 'name' => '等级'],
        ['type_nid' => 1, 'name' => '普卡'],
        ['type_nid' => 2, 'name' => '金卡'],
        ['type_nid' => 3, 'name' => '白金卡'],
    ];

    //信用卡模块开始
    //广告涉及常量
    const BANNER_PROGRESS = 'banner_progress';    //进度查询banner
    const BANNER_GIVEUP = 'banner_giveup';    //代还banner
    const BANNER_GIFT = 'banner_gift';    //办卡有礼banner
    const BANNER_ACTIVATION = 'banner_activation';  //立即激活banner
    //特色精选
    const SPECIAL = 'special';
    //热门推荐
    const SPECIAL_RECOMMEND = 'special_recommend';
    //大额度
    const SPECIAL_AMOUNT = 'special_amount';
    //快速批卡
    const SPECIAL_FAST_BATCH_CARD = 'special_fast_batch_card';
    //新手办卡
    const SPECIAL_NEW_HAND = 'special_newer_card';
    //办卡有礼
    const SPECIAL_GIFT = 'special_gift';
    //用途卡片
    const IMAGE_USAGE = 'image_usage';
    //办卡头条 activation立即激活，raise_quota立即提额
    const HEAD_LINES = [
        ['title' => '刚刚收到信用卡，', 'name' => '立即激活！', 'headline_url' => 'activation'],
        ['title' => '额度太低，', 'name' => '立即提额！', 'headline_url' => 'raise_quota'],
    ];

    //信用卡代还产品对应唯一标识
    const CREGITCARD_TYPE_NID = 'creditcard_payback';

    //取现地址
    const CREDIT_CARD_CASH_LINK = 'https://changhui.liansh.com/nocard_company/#/login?institutionno=100000001010';


}

