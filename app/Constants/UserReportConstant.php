<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 用户报告常量定义
 */
class UserReportConstant extends AppConstant
{
    //付费标识 1免费，2付费
    const PAY_TYPE_FREE = 1;
    const PAY_TYPE_PAY = 2;

    //进行步骤 0 任务开始, 1 芝麻完毕, 2 运营商处理完毕 3 报告处理中, 4 报告生成完毕, 9过期
    const REPORT_STEP_START = 0;
    const REPORT_STEP_ZHIMA = 1;
    const REPORT_STEP_CARRIER = 2;
    const REPORT_STEP_PROCESS = 3;
    const REPORT_STEP_END = 4;
    const REPORT_STEP_EXPIRE = 9;

    // 查询过程提示 11不是vip,12未认证 13您已经是VIP会员，可免费查询哦~ 14普通用户，直接去付费，15支付处理中，不可以进行付款查
    const PAY_TYPE_DEFAULT = 0;
    const PAY_TYPE_NOT_VIP = 11;
    const PAY_TYPE_NOT_ALIVE = 12;
    const PAY_TYPE_VIP_HAVE_FREE = 13;
    const PAY_TYPE_DIRECT_PAY = 14;
    const PAY_TYPE_PAYING = 15;

    //报告支付配置
    //订单类型
    const REPORT_ORDER_TYPE = 'user_report';

    //报告类型
    const REPORT_TYPE = 'credit';

    //商户名称
    const REPORT_MEMBER_NAME = 'jdt';

    //购买项目
    const REPORT_ORDER_PRODUCT_NAME = '信用报告';

    //有效期
    const REPORT_PRODUCT_VALIDITY = '永久有效';

    //订单有效期
    const REPORT_ORDER_EXPIRED_MINUTE = 30;

    //订单描述
    const ORDER_DESC = '【信用报告】';

    //支付渠道
    const PAYMENT_TYPE = 'YBZF';

    //报告任务是付费状态
    const REPORT_TASK_IS_PAY = 2;

    //报告任务付费成功的step
    const REPORT_TASK_STEP = 0;

    //信用报告默认值
    const REPORT_DEFAULT = '未知';


    //信用报告标题
    const REPORT_SAMPLE = '信用报告样本';
    const REPORT_INFO = '信用报告';


    //信用报告分数计算
    //最小打分区间
    const REPORT_MIN_RANGE = 637;
    //最大打分区间
    const REPORT_MAX_RANGE = 772;

    //最小得分区间
    const REPORT_MIN_RANGE_SCORE = 0;
    //最大得分区间
    const REPORT_MAX_RANGE_SCORE = 10;

    //权值
    //黑名单
    const REPORT_BLACKS_WEIGHT = 0.4;
    //注册信息
    const REPORT_REGISTER_WEIGHT = 0.05;
    //机构查询历史
    const REPORT_HISTORY_WEIGHT = 0.25;
    //公积金
    const REPORT_FUND_WEIGHT = 0.3;

}

