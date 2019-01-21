<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 用户模块中使用的常量
 */
class UserConstant extends AppConstant
{
    //用户头像存储路径
    const USER_PHOTO_PREFIX = 'photo';
    //用户身份证照片存储路径
    const USER_ID_CARD_PREFIX = 'identity/idcard';
    //活体照片存储路径
    const USER_ALIVE_PREFIX = 'identity/alive';

    //用户默认头像
    const USER_PHOTO_DEFAULT = 'http://image.jdt.com/production/20171221/privilege/20171221154747-410.png';
}

