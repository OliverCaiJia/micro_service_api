<?php

namespace App\Http\Controllers\V1;

use App\Constants\PopConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\PushFactory;
use App\Strategies\PushStrategy;
use Illuminate\Http\Request;

/**
 * Class PushController
 * @package App\Http\Controllers\V1
 * 推送
 */
class PushController extends Controller
{
    /**
     * 极光推送 —— 接收用户指定设备的registrationId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function putRegistrationIdToCache(Request $request)
    {
        $registrationId = $request->input('registrationId');
        $userId = $request->user()->sd_user_id;
        //存redis
        CacheFactory::putValueToCache('jpush_registration_id_' . $userId, $registrationId);

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


    /**
     * 任务弹窗
     * 0  首页  1  我的  2  积分
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPopup(Request $request)
    {
        $position = $request->input('position');
        //查询需要推送的信息
        $push = PushFactory::fetchPopup($position);
        if (empty($push)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500); //暂无数据
        }

        //执行次数叠加
        PushFactory::updatePopup($push['id']);

        $pushArr = PushStrategy::getPopup($push);

        return RestResponseFactory::ok($pushArr);
    }

    /**
     * 引导页 根据手机像素大小 返回相应大小的图片
     * @is_default 默认, 1是 0否
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchGuidePage(Request $request)
    {
        $data['height'] = $request->input('height', 1);
        $data['width'] = $request->input('width', 1);
        //标识 4 表示引导页广告
        $data['position'] = PopConstant::GUIDE_PAGE_BANNERS_TYPE;
        //比例对应的图片
        //默认值
        $push = PushFactory::fetchGuidePageByType($data);
        if (!$push) {
            //查询一张默认的图片
            $data['is_default'] = 1;
            $push = PushFactory::fetchGuidePageByIsDefault($data);
        }
        if (!$push) {
            //暂无数据
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //执行次数叠加
        PushFactory::updatePopup($push['id']);
        //数据处理
        $pushs = PushStrategy::getPopup($push);

        return RestResponseFactory::ok($pushs);
    }
}