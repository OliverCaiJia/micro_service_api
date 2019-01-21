<?php

namespace App\Http\Controllers\V2;

use App\Constants\CreditConstant;
use App\Constants\HelpConstant;
use App\Events\V1\AddIntegralEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\HelpFactory;
use App\Strategies\HelpStrategy;
use Illuminate\Http\Request;

/**
 * Class HelpController
 * @package App\Http\Controllers\V1
 * 帮助中心
 */
class HelpController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     * 帮助中心
     */
    public function fetchHelps()
    {
        //帮助中心类型
        $helpType = HelpFactory::fetchHelpTypes();
        //帮助中心数据
        $helps = HelpFactory::fetchHelpLists($helpType);
        if (empty($helpType) || empty($helps)) {
            $datas['lists'] = [];
        } else {
            $datas['lists'] = $helps;
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

        //推荐新贷款产品 每天最多1次
        $eventData['typeNid'] = CreditConstant::ADD_INTEGRAL_FEEDBACK_TYPE;
        $eventData['remark'] = CreditConstant::ADD_INTEGRAL_FEEDBACK_REMARK;
        $eventData['max_count'] = CreditConstant::ADD_INTEGRAL_FEEDBACK_COUNT;
        $eventData['typeId'] = CreditFactory::fetchIdByTypeNid($eventData['typeNid']);
        $eventData['score'] = CreditFactory::fetchScoreByTypeNid($eventData['typeNid']);
        $eventData['userId'] = $data['userId'];
        event(new AddIntegralEvent($eventData));

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


}