<?php

namespace App\Http\Controllers\V1;

use App\Constants\ConfigConstant;
use App\Services\Core\Validator\ValidatorService;
use App\Strategies\ConfigStrategy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\RestResponseFactory;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\VersionFactory;

class ConfigController extends Controller
{

    /**
     * 过审
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function iOSPending(Request $request)
    {
        $type = $request->input('type', '');
        //平台类型
        $plat['platType'] = $request->input('platType', 'ios');
        //android渠道类型
        $channel = $request->input('channelType', '');

        // 应用（包壳）类型
        $appType = '';
        $versionCode = '1.0.0';
        if (count($type = explode("_", $type)) > 1) {
            $versionCode = array_pop($type);
            $appType = join("_", $type);
        }
        // 是否需要审核(0为需要审核,1为不需要审核)
        $data['pending'] = 1;
        $plat['appType'] = $appType;
        $plat['versionCode'] = $versionCode;
        //iOS Android 审核
        switch ($plat['platType']) {
            case 'ios':
                $version = VersionFactory::fetchIOSPeding($plat);
                if (isset($version['pending'])) {
                    $data['pending'] = $version['pending'];
                }
                break;
            case 'android':
//                $channels = ConfigConstant::ANDROID_PENDING_CHANNEL;
                $version = VersionFactory::fetchIOSPeding($plat);
                if (!empty($version)) {
                    //根据总表下面的进行
                    $conInfo = VersionFactory::fetchUpgradeConfigInfo($version, $channel);

                    if (!empty($conInfo)) {
                        $data['pending'] = $conInfo['pending'];
                    }
                }

//                if (in_array($channel, $channels) && isset($version['pending'])) {
//                    $data['pending'] = $version['pending'];
//                }
                break;
            default:
                $data['pending'] = 1;
        }

        return RestResponseFactory::ok($data);
    }

    /**
     * 服务端配置对接平台的appkey & appsecret
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAppkey(Request $request)
    {
        //1 ios,2 android
        $terminalType = $request->input('terminalType', 0);

        $params = [];
        //服务端配置appkey & appsecret
        $data = ConfigConstant::CONFIG_APPKEY;

        //魔蝎
        $params['scorpioKey'] = $data['scorpio'];

        //拍拍贷key值配置
        if (1 == $terminalType) {
            $params['ppdKey'] = $data['ppd_ios'];
        } elseif (2 == $terminalType) {
            $params['ppdKey'] = $data['ppd_android'];
        }

        //数据处理
        $res = ConfigStrategy::getAbutPlatformAppkey($params);

        return RestResponseFactory::ok($res);
    }

}
