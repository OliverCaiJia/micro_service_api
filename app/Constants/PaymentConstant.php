<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 支付使用的常量
 */
class PaymentConstant extends AppConstant
{
    //终端识别类型
    const YIBAO_TERMINAL_TYPE = 0;

    //订单编号长度
    const PAYMENT_PRODUCT_NUMBER_LENGTH = 32;

    //订单有效期
    const ORDER_EXPIRED_MINUTE = 30;

    //易宝微信支付状态 0,不显示  1,测试显示  2,正式显示
    const PAYMENT_YIBAO_WECHAT_PAY_STATUS = 0;

    //易宝支付宝支付状态 0,不显示  1,测试显示  2,正式显示
    const PAYMENT_YIBAO_ALI_PAY_STATUS = 0;

}

