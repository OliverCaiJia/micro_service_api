<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * Class UserIdentityConstant
 * @package App\Constants
 * 用户身份认证常量
 */
class UserIdentityConstant extends AppConstant
{
    //认证状态【9通过,1face通过,2天创通过,3活体通过,4公安部通过】
    const AUTHENTICATION_STATUS_FACE = 1;
    const AUTHENTICATION_STATUS_TIAN = 2;
    const AUTHENTICATION_STATUS_ALIVE = 3;
    const AUTHENTICATION_STATUS_POLICE = 4;
    const AUTHENTICATION_STATUS_FINAL = 9;

    //证件类型【0身份证】
    const CERTIFICATE_TYPE_IDCARD = 0;

    //验证类型
    const AUTHENTICATION_TYPE_FACEID = 'faceid';
    const AUTHENTICATION_TYPE_TIAN = 'tianchuang';

    //身份证识别精度值
    const ID_CARD_LEGALITY_VALUE = 0.9;
    //quality 每一个人脸都会有一个质量判断的分数。
    const ID_CARD_QUALITY_VALUE = 80;
}