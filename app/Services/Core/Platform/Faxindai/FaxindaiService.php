<?php

namespace App\Services\Core\Platform\Faxindai;

use App\Services\Core\Platform\PlatformService;

/**
 * 发薪贷
 */
class FaxindaiService extends PlatformService
{
    //历史地址
    //const URL = 'http://116.236.225.158:8020/fxd-h5/page/thirdIndex.html';
    const URL = 'http://h5.faxindai.com:8020/fxd-h5/page/thirdIndex.html?merchant_code_=M12_20170315_10s2df000&sdfmobile_phone_=';

    /**
     * @param $mobile
     * @param $cooperate
     * @return string
     * 发薪贷 —— 应急贷对接地址
     */
    public static function fetchFaxindaiUrl($datas)
    {
        $mobile = $datas['user']['mobile']; //手机号
        $page = self::URL; //地址

        $page = str_replace('mobile_phone_=','',$page);
        $page = rtrim($page,'&');
        $vargs = http_build_query([
            'mobile_phone_'  => $mobile,    //手机号码
        ]);
        //'merchant_code_' => 'M11_20151001_00017'    //app名称

        $url   = $page . '&' . $vargs;
        $page  = $url;

        return $page ? $page : '';
    }


}
