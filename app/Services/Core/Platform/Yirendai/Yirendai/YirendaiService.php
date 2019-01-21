<?php

namespace App\Services\Core\Platform\Yirendai\Yirendai;

use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Models\Factory\DeviceFactory;
use App\Services\Core\Platform\PlatformService;
use App\Strategies\DeviceStrategy;

/**
 * Class YirendaiService
 * @package App\Services\Core\Platform\Yirendai\Yirendai
 * 宜人贷
 */
class YirendaiService extends PlatformService
{
    //测试环境地址
//    const URL_START = 'http://html-fastmode.yixinonline.com/liuchao/';
//    const URL_END = '#/app/am-forecast';

    //正式环境地址
    const URL = 'https://html5-v-fastmode.yirendai.com';

//    const URL = 'https://html5-fastmode.yirendai.com';
    

//     const URL = 'https://html5-v-fastmode.yirendai.com';
//    const URL = 'http://html5-fastmode.yixinonline.com/liuchaovv/';
    //渠道号
    const PLATFORM_CODE = 'sDSFApp';
    //默认注册地址
    //const REGISTER_URL = 'https://www.yirendai.com/lp/140/2/?siteId=2460';

    /**
     * @param $datas
     * @return string
     * 宜人贷对接
     */
    public static function fetchYirendaiUrl($datas)
    {
        $mobile = $datas['user']['mobile']; //手机号
        $page = $datas['page']; //地址

        //获取设备信息数据
        $device = DeviceFactory::fetchDevicesByUserId($datas['userId']);
        //判断是否是移动设备
        $isPhone = UserAgent::i()->isPhone();
        //验证是否是浏览器
        $isBrowser = self::checkBrowser();
        //没有定位信息，直接返回注册页面地址
        if ($isBrowser || empty($device) || !$isPhone || empty($device['lon_lat'])) {
            return $page;
        }
        //数据处理
        $devices = DeviceStrategy::getDevicesFromUserId($device);

        //必须参数为空则返回注册地址
        if (empty($devices['systemModel']) || empty($devices['systemPhone']) || empty($devices['clientIdentify'])) {
            return $page;
        }

        //地址携带参数
        $vargs = http_build_query([

            'clientIdentify' => $devices['clientIdentify'], //手机客户端唯一标示码
            'systemModel' => $devices['systemModel'],  //手机型号
            'systemPhone' => $devices['systemPhone'],  //手机系统平台
            'lng' => $devices['lng'],  //经度
            'lat' => $devices['lat'],  //纬度
            'userId' => $datas['userId'],  //渠道方用户的唯一标识
            'phone' => $mobile,  //渠道方用户手机号
            'platformCode' => self::PLATFORM_CODE,  //渠道号

        ]);
        //拼上参数直接访问地址
//        $url = self::URL_START . '?' . $vargs . self::URL_END;
        $url = self::URL . '?' . $vargs;
        //SLogger::getStream()->info('宜人贷', ['url' => $url]);

        return $url ? $url : '';
    }

    /**
     * @return bool
     * 验证是否是浏览器打开
     */
    public static function checkBrowser()
    {
        $userAgent = UserAgent::i()->getUserAgent();
        //判断Mozilla是否存在于user agent中
        if (stristr($userAgent, 'Mozilla')) {
            return true;
        }

        return false;
    }
}