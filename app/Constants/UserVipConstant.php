<?php

namespace App\Constants;

/**
 * VIP用户中使用的常量
 */
class UserVipConstant extends AppConstant
{
    //终端识别类型
    const YIBAO_TERMINAL_TYPE = 0;

    //vip编号长度
    const PAYMENT_PRODUCT_NUMBER_LENGTH = 32;

    //客服电话
    const CONSUMER_HOTLINE = '4000390718';

    //会员类型
    const VIP_TYPE_NID = 'vip_default';

    //支付类型
    const PAYMENT_TYPE = 'YBZF';

    //订单类型
    const ORDER_TYPE = 'user_vip';

    //会员价格
    const ORDER_MEMBER_MONEY = '99';

    //会员原价
    const MEMBER_PRICE = '299';

    //商户名称
    const ORDER_DEALER_NAME = '速贷之家';

    //购买项目
    const ORDER_PRODUCT_NAME = '会员充值';

    //订单描述
    const ORDER_DESC = '【会员充值】';

    //订单有效期
    const ORDER_EXPIRED_MINUTE = 30;

    //普通用户
    const VIP_TYPE_NID_VIP_COMMON = 'vip_common';

    //下款率:普通用户
    const MEMBER_COMMON_DOWN_RATE = '38%';

    //下款率:普通会员
    const MEMBER_VIP_DOWN_RATE = '86%';

    //贷款产品：普通用户
    const MEMBER_COMMON_LOAN_PRODUCT_NID = 'vip_common_loan_product';

    //贷款产品:普通会员
    const MEMBER_VIP_LOAN_PRODUCT_NID = 'vip_default_loan_product';

    //会员加积分 1.5倍积分
    const VIP_ADD_CREDIT = 'vipCredit';


}

