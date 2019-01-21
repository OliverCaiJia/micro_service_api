<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 用户模块中使用的常量
 */
class ProductConstant extends AppConstant
{
    //计算器
    const   REPAYMENT = '一次性还款(元)';
    const  EACH_INTEREST = '每期利息(元)';

    //申请人评价
    const COUHE = '凑合';
    const ORDINARY = '普通';
    const GOOD = '较好';
    const  BETTER = '良好';
    const  BEST = '优秀';
    const EXCELLENT = '爆款!';

    //放款速度描述
    const FAST_SPEED_IMPROVISE = '普快';
    const FAST_SPEED_ORDINARY = '特快';
    const FAST_SPEED_GOOD = '超快';
    const FAST_SPEED_BETTER = '风速';
    const FAST_SPEED_BEST = '音速';
    const FAST_SPEED_EXCELLENT = '光速';

    //产品搜索标签配置
    //我需要
    const TAG_CON_NEED = 'con_loan_need';
    //我有
    const TAG_CON_HAS = 'con_loan_has';

    //计算器  Calculators
    const CALCULATOR_MONEY = [
        "500元", "1000元", "1500元", "2000元", "3000元", "4000元", "5000元", "6000元", "7000元", "8000元", "9000元", "10000元", "15000元", "20000元", "30000元", "50000元", "10万元", "20万元",
    ];
    const CALCULATOR_TERM = [
        "7天", "14天", "21天", "1个月", "2个月", "3个月", "4个月", "5个月", "6个月", "9个月", "1年", "1.5年", "2年", "3年",
    ];
    //第三版产品计算器修改 期限只展示 日/月
    const SECOND_EDITION__CALCULATOR_TIME = [
        "7天", "14天", "21天", "1个月", "2个月", "3个月", "4个月", "5个月", "6个月", "9个月", "12个月", "18个月", "24个月", "36个月",
    ];

    const CALCULATOR_MONEY_INT = [
        "500", "1000", "1500", "2000", "3000", "4000", "5000", "6000", "7000", "8000", "9000", "10000", "15000", "20000", "30000", "50000", "100000", "200000",
    ];
    const CALCULATOR_TERM_INT = [
        "7", "14", "21", "30", "60", "90", "120", "150", "180", "270", "360", "540", "720", "1080",
    ];

    //首页新上线产品 引导语
    const  PRODUCT_ONLINE_CONFIG = 'con_product_online_remark';
    const  PRODUCT_ONLINE_REMARK = '通过筛选，成功入驻速贷之家';

    //产品详情——计算器 倍率
    const PRODUCT_TIMES = 'times';
    //放款速度
    const PRODUCT_LOAN_TIME = 'loan_time';

    //vip可看产品个数
    const PRODUCT_VIP_COUNT = 257;

    //审批条件
    const PRODUCT_DETAIL_APPROVAL_CONDITION = 'product_tag_type_condition';
    //信用贴士
    const PRODUCT_DETAIL_CREDIT_TIPS = 'product_tag_type_tips';

    //首页良心推荐
    const PRODUCT_RECOMMEND = 'recommend_default';

    //滑动推荐
    //高通过
    const PRODUCT_HIGH_THROUGH = 'high_through';
    //三步申请
    const PRODUCT_THREE_STEP_APPLICATION = 'three_step_application';
    //秒审批
    const PRODUCT_SECOND_APPROVAL = 'second_approval';
    //不查征信
    const PRODUCT_NOT_NEED_CREDIT = 'not_need_credit';

    //黑名单产品标签
    //已申请
    const PRODUCT_TAG_TYPE_APPLIED = 'product_tag_type_applied';
    //不符合
    const PRODUCT_TAG_TYPE_MISMATCH = 'product_tag_type_mismatch';
    //我有
    const   PRODUCT_TAG_TYPE_HAS = 'product_tag_type_has';
    //我需要
    const   PRODUCT_TAG_TYPE_NEED = 'product_tag_type_need';

    //平台产品标签配置表
    //已申请
    const CON_PRODUCT_TAG_TYPE_APPLIED = 'con_product_tag_type_applied';
    //不符合 inconformity
    const CON_PRODUCT_TAG_TYPE_MISMATCH = 'con_product_tag_type_mismatch';

    //速贷大全标签
    const PRODUCT_TAG_TYPE_LOAN = 'product_tag_type_loan';


    //第四版计算器名词解释
    //总还款金额
    const TOTAL_DESC = '';
    //到账金额
    const ACCOUNT_DESC = '';
    //利息和服务费
    const INTEREST_DESC = '';
    //日、月还款金额
    const REPAY_DESC = '';

}

