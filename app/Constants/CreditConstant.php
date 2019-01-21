<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 积分模块中使用的常量
 */
class CreditConstant extends AppConstant
{
    //积分兑换金额
    const EXCHANGE_CREDIT = 1500;
    const EXCHANGE_MONRY = 4;
    //默认空
    const ACCOUNT_NULL = 0;
    //状态值  1已完成 0未完成
    const SIGN_FULL = 1;
    const SIGN_EMPTY = 0;
    //默认空
    const DEFAULT_EMPTY = 0;
    //积分兑现金
    const CREDIT_CASH_REMARK = '积分兑换现金';
    const CREDIT_CASH_TYPE = 'credit_cash';
    //积分 产品申请
    const PRODUCT_APPLY_REMARK = '邀请好友并申请';
    const PRODUCT_APPLY_TYPE = 'product_apply';
    const PRODUCT_APPLY_MONEY = 0.4;
    //积分页  我的积分下的描述
    const CREDIT_REMARK_TYPE = 'con_credit_remark';
    //注册中邀请好友
    const REGISTER_INVITE_REMARK = '邀请好友注册，送积分';
    const REGISTER_INVITE_TYPE = 'register_invite';

    //信用资料填写完整加积分
    const USERINFO_COMPLETE_REMARK = '完善个人信息加积分';
    const USERINFO_COMPLETE_TYPE = 'credit_complete_userinfo';

    //兑充 修改积分负值
    const  EDIT_CREDIT_REMARK = '修订积分负值';
    const  EDIT_CREDIT_TYPE = 'revised_credit';
    //兑充 修改账户负值
    const EDIT_ACCOUNT_REMARK = '修订账户负值';
    const EDIT_ACCOUNT_TYPE = 'revised_account';

    //催审扣积分
    const REDUCR_URGE_CREDIT_REMARK = '催审扣积分';
    const REDUCE_URGE_CREDIT_TYPE = 'reduce_urge_credit';


    //赚积分
    //赚积分新人注册
    const  ADD_INTEGRAL_USER_REGISTER_TYPE = 'sd_user_registers';
    const  ADD_INTEGRAL_USER_REGISTER_REMARK = '新人注册';
    //赚积分首次设置头像
    const  ADD_INTEGRAL_USER_PHOTO_TYPE = 'sd_user_photo';
    const  ADD_INTEGRAL_USER_PHOTO_REMARK = '首次设置头像';
    //赚积分首次设置用户名
    const  ADD_INTEGRAL_USER_USERNAME_TYPE = 'sd_user_username';
    const  ADD_INTEGRAL_USER_USERNAME_REMARK = '设置用户名';
    //每日签到
    const  ADD_INTEGRAL_USER_SIGN_TYPE = 'sd_user_sign';
    const  ADD_INTEGRAL_USER_SIGN_REMARK = '每日签到';
    //发表评论 每天最多5次
    const  ADD_INTEGRAL_USER_COMMENT_TYPE = 'sd_user_comment';
    const  ADD_INTEGRAL_USER_COMMENT_REMARK = '发表评论';
    const  ADD_INTEGRAL_USER_COMMENT_COUNT = 5;
    //推荐新贷款产品
    const  ADD_INTEGRAL_FEEDBACK_PRODUCT_NAME_TYPE = 'product_feedback_accept';
    const  ADD_INTEGRAL_FEEDBACK_PRODUCT_NAME_REMARK = '推荐新贷款产品';
    const  ADD_INTEGRAL_FEEDBACK_PRODUCT_NAME_COUNT = 2;
    //意见反馈 每天最多1次
    const  ADD_INTEGRAL_FEEDBACK_TYPE = 'sd_feedback';
    const  ADD_INTEGRAL_FEEDBACK_REMARK = '意见反馈';
    const  ADD_INTEGRAL_FEEDBACK_COUNT = 1;


    //签到1.5倍积分
    const ADD_INTEGRAL_DOUBLE_USER_SIGN_TYPE = 'sd_user_sign_double';

}