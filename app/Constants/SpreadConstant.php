<?php

namespace App\Constants;

/**
 * 推广常量配置
 * Class SpreadConstant
 * @package App\Constants
 */
class SpreadConstant extends AppConstant
{
    /**
     * 推广地域相关
     */
    const SPREAD_DEVICE = [
        '延边' => '延边朝鲜族自治州',
        '黔南' => '黔南布依族苗族自治州',
        '巴音郭楞' => '巴音郭楞蒙古自治州',
        '西双版纳' => '西双版纳傣族自治州',
        '伊犁' => '伊犁哈萨克自治州',
        '凉山' => '凉山彝族自治州',
    ];

    //推送金额界限
    const  SPREAD_MONEY_LIMIT_A = 10000;
    const SPREAD_MONEY_LIMIT_B = 30000;
    //组
    const SPREAD_GROUP = [
        'group_a', 'group_b', 'group_c',
    ];

    //分组唯一标识
    const SPREAD_GROUP_A = 'group_a';
    const SPREAD_GROUP_B = 'group_b';
    const SPREAD_GROUP_C = 'group_c';
    const SPREAD_GROUP_D = 'group_d';
    const SPREAD_GROUP_AB = 'group_ab';
    const SPREAD_GROUP_AC = 'group_ac';
    const SPREAD_GROUP_BC = 'group_bc';
    const SPREAD_GROUP_ABC = 'group_abc';

    //来源标识
    const SPREAD_FORM = 2;

    //存储推送产品限制次数的key值
    const SPREAD_QUOTA = 'oneloan_';

    //基本信息进度
    const PROGRESS_BASIC_VALUE = 20;
    const PROGRESS_BASIC_COUNT = 3;
    //工作信息进度
    const PROGRESS_WORKING_VALUE = 40;
    const PROGRESS_WORKING_OFFICE_COUNT = 6;
    const PROGRESS_WORKING_CIVIL_COUNT = 4;
    const PROGRESS_WORKING_BUSINESS_COUNT = 3;
    //资产信息进度
    const PROGRESS_PROPERTY_VALUE = 25;
    const PROGRESS_PROPERTY_COUNT = 3;
    //信用信息进度
    const PROGRESS_CREDIT_VALUE = 10;
    const PROGRESS_CREDIT_COUNT = 2;
}