<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 系统配置模块中使用的常量
 */
class CommentConstant extends AppConstant
{
    //敏感词 sensitive
    const SENSITIVE = ['支付宝', '垃圾', '高利贷', '忽悠人', 'QQ', '骗', '勿扰', '无担保', '微信'];
    //申请状态 评论结果  result 1，钱到手 2，未通过 3 其他 4 申请中 ， 5 已获批
    const RESULT_ONE = '已下款';
    const RESULT_TWO = '被拒绝';
    const RESULT_THREE = '其他';
    const RESULT_FOUR = '已申请';
    const RESULT_FIVE = '出额度';
    const RESULT_SIX = '未申请';

    //申请状态 评论结果  result 1，已下款 2，被拒绝 3 其他 4 已申请 ， 5 出额度，6 未申请，7已批贷
    const RESULT_SUCCESS = '已下款';
    const RESULT_FAIL = '被拒绝';
    const RESULT_OTHER = '其他';
    const RESULT_APPLICATION = '已申请';
    const RESULT_APPROVED = '出额度';
    const RESULT_NOT_APPLY = '未申请';
    const RESULT_APPROVED_LOAN = '已批贷';

    //version3 评论结果 result  7已批贷 , 2 被拒绝 , 4 等待审批 , 6 未申请
    const RESULT_VERSION3_ALL = '全部';
    const RESULT_VERSION3_FAIL = '被拒绝';
    const RESULT_VERSION3_APPLICATION = '等待审批';
    const RESULT_VERSION3_APPROVED_LOAN = '已批贷';
    const RESULT_VERSION3_NOT_APPLY = '尚未申请';

    //审批时间
    const APPLY_TIME = 'loan_time';

    //审批时间描述 10分钟内  约1小时  约半天  约1天  约2天
    const APPLY_TIME_ONE = '10分钟内';
    const APPLY_TIME_TWO = '约1小时';
    const APPLY_TIME_THREE = '约半天';
    const APPLY_TIME_FOUR = '约1天';
    const APPLY_TIME_FIVE = '约2天';

    //审批时间的值
    const APPLY_VALUE_ONE = '600';
    const APPLY_VALUE_TWO = '1800';
    const APPLY_VALUE_THREE = '10800';
    const APPLY_VALUE_FOUR = '28800';
    const APPLY_VALUE_FIVE = '86400';

}

