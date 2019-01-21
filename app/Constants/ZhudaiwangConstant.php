<?php
namespace App\Constants;

/**
 * 助贷网常量定义
 * Class ZhudaiwangConstant
 * @package App\Constants
 */
class ZhudaiwangConstant extends AppConstant
{
    //助贷网城市分布
    const PUSH_CITYS = ['深圳市', '上海市', '北京市', '广州市', '杭州市', '南京市'];

    //50条
    const PUSH_CITY_F = ['深圳市', '上海市', '北京市', '杭州市'];
    const PUSH_CITY_F_KEY = 'oneloan_zhudai_fifty';
    const PUSH_CITY_F_LIMIT = 50;

    //10条
    const PUSH_CITY_T = ['广州市'];
    const PUSH_CITY_T_KEY = 'oneloan_zhudai_ten';
    const PUSH_CITY_T_LIMIT = 10;

    //30条
    const PUSH_CITY_TH = ['南京市'];
    const PUSH_CITY_TH_KEY = 'oneloan_zhudai_thirty';
    const PUSH_CITY_TH_LIMIT = 30;

    //开关
    const PUSH_CITY_SWITCH = 'oneloan_zhudai_switch';


}