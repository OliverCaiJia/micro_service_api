<?php

namespace App\Http\Controllers\V3;

use App\Constants\CreditConstant;
use App\Events\V1\AddIntegralEvent;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\CreditStatusFactory;
use App\Services\Core\WangYiYunDun\CloudShield\CloudShieldService;
use App\Models\Factory\UserFactory;
use Illuminate\Http\Request;

/**
 * Class UserController
 * @package App\Http\Controllers\V3
 * 用户中心
 */
class UserController extends Controller
{

    function index(Request $request)
    {
        return RestResponseFactory::ok(null, 'OK');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 修改用户名 加积分
     */
    public function updateUsername(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['username'] = $request->input('username');

        //判断用户名的唯一性
        $username = UserFactory::fetchUsernameByName($data);
        if ($username) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1120), 1120);
        } else {
            $dataId = "opensns";
            $text = CloudShieldService::UserMain($dataId, $data['userId'], $data['username']);
            if ($text != 0) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2102), 2102);
            }
        }

        $res = UserFactory::updateUsername($data);
        if (!$res) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        //首次设置用户名加积分
        $eventData['typeNid'] = CreditConstant::ADD_INTEGRAL_USER_USERNAME_TYPE;
        $eventData['remark'] = CreditConstant::ADD_INTEGRAL_USER_USERNAME_REMARK;
        $eventData['typeId'] = CreditFactory::fetchIdByTypeNid($eventData['typeNid']);
        $eventData['score'] = CreditFactory::fetchScoreByTypeNid($eventData['typeNid']);
        $eventData['userId'] = $data['userId'];
        event(new AddIntegralEvent($eventData));

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }


}
