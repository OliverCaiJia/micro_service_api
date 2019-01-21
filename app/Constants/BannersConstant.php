<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 系统配置模块中使用的常量
 */
class BannersConstant extends AppConstant
{
    //广告 default
    const BANNER_TYPE_BANNER = 'default';
    //速贷推荐 banner_recommend
    const BANNER_TYPE_RECOMMEND = 'banner_recommend';
    //账单导入广告
    const BANNER_BILL_IMPORT = 'banner_bill_import';
    //2.7.5 广告轮播
    const BANNER_TYPE_NEW_BANNER = 'banner';
    //会员中心广告
    const BANNER_TYPE_VIP_CENTER = 'banner_vip_center';

    //热门贷款 hot_loan 2
    const BANNER_CREDIT_CARD_TYPE_HOT_LOAN = 'hot_loan';
    //分类专题 default 1
    const BANNER_CREDIT_CARD_TYPE_SPECIAL = 'default';
    //新分类专题 special 3
    const BANNER_CREDIT_CARD_TYPE_NEW_SPECIAL = 'special';
    //第三版 分类专题
    const BANNER_CREDIT_CARD_TYPE_THIRD_EDITION_SPECIAL = 'third_edition_special';
    //第四版 分类专题  轮播样式
    const BANNER_CAROUSEL_SPECIAL = 'carousel_special';


    //第二版 速贷推荐
    const BANNER_CREDIT_CARD_TYPE_SECOND_EDITION_RECOMMEND = 'second_edition_recommend';
}

