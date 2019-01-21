<?php

namespace App\Constants;

use App\Constants\AppConstant;
use App\Services\AppService;
use Illuminate\Support\Facades\App;

/**
 * 帮助中心使用常量
 */
class HelpConstant extends AppConstant
{
    //信用&借款
    const HELP_CREDIT_LOAN = 'credit_loan';
    //速贷币
    const HELP_QUICK_MONEY = 'quick_money';
    //如何借款
    const HELP_HOW_LOAN = 'how_loan';

    //官方热线
    const HELP_OFFICIAL_HOTLINE = '4000390718';
    //官方QQ群
//    const HELP_OFFICIAL_QQ = '497701352';
//    const HELP_OFFICIAL_QQ_IOS_KEY = '953d07afa3874693a0cbd9f76fd528fc60f7e0aca00010b0ac608b010438fb03';
//    const HELP_OFFICIAL_QQ_ANDROID_KEY = 'o3udeWOBqeEkd3ft_UMBPd4a4WfnQq1O';
//    const HELP_OFFICIAL_QQ_WEB_KEY = '953d07afa3874693a0cbd9f76fd528fc60f7e0aca00010b0ac608b010438fb03';

    //官方QQ群 第二版
    const HELP_OFFICIAL_QQ = '167411270';
    const HELP_OFFICIAL_QQ_IOS_KEY = '05a44737a6bfa0d80eb76889d45a20111adb8de168f68950aa05e9a23f0a0455';
    const HELP_OFFICIAL_QQ_ANDROID_KEY = 'tu6lcm7Ykur3TLRPEDw_rCMdjjzzuxNt';
    const HELP_OFFICIAL_QQ_WEB_KEY = '05a44737a6bfa0d80eb76889d45a20111adb8de168f68950aa05e9a23f0a0455';

    //协议中心
    const AGREEMENTS = [
        [
            'id' => 1,
            'name' => '速贷之家《注册服务协议》',
            'url' => AppService::API_URL . '/view/users/identity/use',
        ],
        [
            'id' => 2,
            'name' => '速贷之家《会员服务协议》',
            'url' => AppService::API_URL . '/view/users/identity/membership',
        ],
        [
            'id' => 3,
            'name' => '闪信《授权协议》',
            'url' => AppService::API_URL . '/view/users/report/agreement',
        ],
    ];

}

