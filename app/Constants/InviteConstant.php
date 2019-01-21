<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 用户模块中使用的常量
 */
class InviteConstant extends AppConstant
{
    //短信邀请
    const INVITE_FROM_SMS = 'sms_invite';
	//分享邀请
	const  INVITE_FROM_SHARE = 'share_invite';
    //邀请状态 1邀请中，2已注册，3已申请
    const INVITE_ING = 1;
    const INVITE_REGISTER = 2;
    const INVITE_ALLPY = 3;
    //已申请 加钱值
    const APPLY_MONEY = 0.40;
}

