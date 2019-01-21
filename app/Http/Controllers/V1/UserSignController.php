<?php

namespace App\Http\Controllers\V1;

use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Factory\UserVipFactory;
use App\Strategies\CreditStrategy;
use Illuminate\Http\Request;
use App\Models\Orm\UserSign;
use App\Models\Orm\UserCreditType;
use App\Models\Chain\UserSign\DoSignHandler;

/**
 * Class UserinfoController
 * @package App\Http\Controllers\V1
 * 用户签到
 */
class UserSignController extends Controller
{
    /**
     * 用户签到
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sign(Request $request)
    {
        $userId = $request->user()->sd_user_id;
        $data['type'] = $request->input('signType', '');

        if ($this->signed($userId)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(9103), 9103);
        }

        //判断是否是会员
        $data['userVipType'] = UserVipFactory::fetchUserVipToTypeByUserId($userId);
        // 获取积分类型
        $type_nid = CreditStrategy::getSignTypeNid($data);
        $type = UserCreditType::where('type_nid', $type_nid)->where('status', 1)->first();
        if (!$type) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(9105), 9105);
        }

        $params = [
            'user_id' => $userId,
            'type' => $type_nid,
            'income' => $type->score,
            'remark' => $type->remark,
            'sign_ip' => Utils::ipAddress(),
        ];

        // 调用签到责任链
        $signHandler = new DoSignHandler($params);
        $res = $signHandler->handleRequest();

        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }

        return RestResponseFactory::ok($res);
    }

    /** 是否签到
     * @param $id
     * @return bool
     */
    private function signed($id)
    {
        $model = UserSign::where('user_id', $id)->first();
        if ($model) {
            $now = strtotime(date('Y-m-d', time()));
            $signed = strtotime($model->sign_at);
            return $now == $signed;
        }

        return false;
    }

}