<?php

namespace App\Http\Controllers\V2;

use App\Constants\PopConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\UserAgent;
use App\Http\Controllers\Controller;
use App\Models\Factory\PushFactory;
use App\Strategies\PushStrategy;
use Illuminate\Http\Request;

/**
 * Class PushController
 * @package App\Http\Controllers\V2
 * 推送
 */
class PushController extends Controller
{
    /**
     * 获取极光推送的registration_id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchJpushRegId(Request $request)
    {
        $data['registration_id'] = $request->input('registrationId', 0);
        $data['user_id'] = empty($request->user()->sd_user_id) ? 0 : $request->user()->sd_user_id;
        $data['type'] = PushStrategy::getPlatformType();
        $data['agent'] = UserAgent::i()->getUserAgent();
        $result = PushFactory::addJpushInfo($data);
        if ($result) {

            return RestResponseFactory::ok();
        }

        return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2101), 2101);
    }


    /**
     * 批量弹窗
     * 0  首页  1  我的  2  积分
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPopUp(Request $request)
    {
        $data['position'] = $request->input('position');
        //查询需要推送的信息
        $data['versionCode'] = PopConstant::PUSH_VERSION_CODE_ONELOAN;
        $push = PushFactory::fetchPopups($data);
        if (empty($push)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500); //暂无数据
        }

        $pushArr = PushStrategy::getPopups($push);

        return RestResponseFactory::ok($pushArr);
    }

    /**
     * 修改点击次数统计
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePopupCount(Request $request)
    {
        $pushId = $request->input('pushId');
        //执行次数叠加
        $res = PushFactory::updatePopup($pushId);

        if ($res) {
            return RestResponseFactory::ok(RestUtils::getStdObj());
        }
        //出错了，请刷新重试
        return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
    }

}