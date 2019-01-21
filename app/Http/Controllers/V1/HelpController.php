<?php

namespace App\Http\Controllers\V1;

use App\Constants\HelpConstant;
use App\Helpers\LinkUtils;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\HelpFactory;
use App\Models\Factory\ProductFactory;
use Illuminate\Http\Request;

/**
 * Class HelpController
 * @package App\Http\Controllers\V1
 * 帮助中心
 */
class HelpController extends Controller
{
    /**
     *  帮助中心
     */
    public function fetchHelpLists()
    {
        //帮助中心类型
        $helpTypeArr = HelpFactory::fetchHelpTypes();
        //帮助中心数据
        $helpArr = HelpFactory::fetchHelpLists($helpTypeArr);
        if (empty($helpTypeArr) || empty($helpArr)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($helpArr);
    }

    /**
     * @return mixed
     * 帮助中心 —— android 调 h5 的帮助中心连接地址
     */
    public function fetchHelpsToAndroid()
    {
        $android['help_link'] = LinkUtils::getHelpsToAndroid();
        return RestResponseFactory::ok($android);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 帮助中心 —— 提问&反馈
     */
    public function createFeedback(Request $request)
    {
        //接收反馈信息
        $data = $request->all();
        $data['userId'] = $request->user()->sd_user_id;

        //添加反馈
        $feedback = HelpFactory::createFeedback($data);
        if (empty($feedback)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2103), 2103);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 关于我们
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchShareOurs(Request $request)
    {
        $varia['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //关于我们
        $data['share_our'] = LinkUtils::shareOur();
        //商务合作
        $data['business_cooperation'] = LinkUtils::BusinessCooperation();
        //不想看产品ids
        $varia['blackIds'] = ProductFactory::fetchBlackIdsByUserId($varia);
        //不想看产品总个数
        $black_counts = ProductFactory::fetchBlackCountsById($varia);
        $data['black_counts'] = empty($black_counts) ? '' : $black_counts . '款产品';

        return RestResponseFactory::ok($data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 获取分类中心数据
     */
    public function fetchHelpTypes()
    {
        $helpType = HelpFactory::fetchHelpTypes();

        if (empty($helpType)) {
            $datas['lists'] = [];
        } else {
            //图片处理
            $helpType = HelpFactory::fetchHelpTypeImg($helpType);
            $datas['lists'] = $helpType;
        }
        //官方热线
        $datas['official_hotline'] = HelpConstant::HELP_OFFICIAL_HOTLINE;
        $datas['official_qq'] = HelpConstant::HELP_OFFICIAL_QQ;
        $datas['official_qq_ios_key'] = HelpConstant::HELP_OFFICIAL_QQ_IOS_KEY;
        $datas['official_qq_android_key'] = HelpConstant::HELP_OFFICIAL_QQ_ANDROID_KEY;
        $datas['official_qq_web_key'] = HelpConstant::HELP_OFFICIAL_QQ_WEB_KEY;

        return RestResponseFactory::ok($datas);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 获取分类下帮助内容
     */
    public function fetchHelpsById(Request $request)
    {
        $typeId = $request->input('typeId');

        $helps = HelpFactory::fetchHelpsByTypeId($typeId);
        if (!$helps) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($helps);
    }


}