<?php

namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Http\Controllers\Controller;
use App\Models\Factory\UserFactory;
use Illuminate\Http\Request;
use App\Services\Core\Zhima\ZhimaService;
use App\Models\Factory\ZhimaFactory;
use App\Helpers\RestUtils;
use App\Models\Chain\UserZhima\DoZhimaHandler;
use App\Helpers\Utils;

/**
 * @package App\Http\Controllers\V1
 */
class ZhimaController extends Controller
{
    /** 芝麻信用授权跳转
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getScore(Request $request)
    {
        // 参数
        $http = $request->all();

        // 获取Openid
        $zhima_str = ZhimaService::i()->getScoreOpenId($http['params'], $http['sign']);
        // 从字符串中获取openid/idcard
        preg_match("/open_id=(\d*?)&/u", $zhima_str, $openId);
        // 无支付宝账号
        if (empty($openId)) {
            $error_meg = RestUtils::getErrorMessage(9111);
            return view('app.jdt.errors.error_static', ['error' => $error_meg]);
//            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(9111), 9111);
        }

        preg_match("/state=(\w*?)&/u", $zhima_str, $info);
        $openId = $openId[1];
        $idcard = $info[1];
        // 获取原先分数
        $oldScore = ZhimaFactory::getOldScore($openId);
        // 获取芝麻分数
        $params = ZhimaService::i()->getZhimaCreditScore($openId);
        // 获取行业白名单
        $list = ZhimaService::i()->getZhimaCreditWatchlist($openId);

        // 用户id
        $userId = UserFactory::getUserIdByIdCard($idcard);
        // 用户姓名
        $name = UserFactory::getRealNameByIdCard($idcard);
        // 用户手机号
        $mobile = UserFactory::fetchMobile($userId);

        // 更新授权状态
        $res = ZhimaFactory::updateTaskStatus(['where' => 0, 'userId' => $userId, 'step' => 1]);
        if (!$res) {
            //return RestResponseFactory::ok(['msg' => '更新授权状态失败']);
            return RestResponseFactory::ok(RestUtils::getStdObj(), '更新授权状态失败', 1);
        }

        $watch = [
            'user_id' => $userId,
            'is_matched' => $list['is_matched'],
            'details' => isset($list['details']) ? json_encode($list['details']) : '',
            'biz_no' => $list['biz_no'],
            'created_at' => date('Y-m-d H:i:s', time()),
            'updated_at' => date('Y-m-d H:i:s', time()),
            'created_ip' => Utils::ipAddress(),
            'updated_ip' => Utils::ipAddress(),
        ];

        $params['openId'] = $openId;
        $params['userId'] = $userId; // userid
        $params['idcard'] = $idcard;
        $params['name'] = $name;
        $params['phone'] = $mobile;
        $params['identityType'] = 2;
        $params['score_old'] = $oldScore;
        $params['score_new'] = $params['zm_score'];
        $params['transactionId'] = ZhimaService::i()->getTransactionId();
        $params['watch'] = $watch;

        $model = new DoZhimaHandler($params);
        $res = $model->handleRequest();
        if (isset($res['error'])) {
            // 跳转
            return redirect()->route('v1.zhima.failure');
        }

        return redirect()->route('v1.zhima.success');
    }

    // 成功跳转路由地址
    public function success()
    {
    }

    // 失败跳转路由地址
    public function failure()
    {
    }
}