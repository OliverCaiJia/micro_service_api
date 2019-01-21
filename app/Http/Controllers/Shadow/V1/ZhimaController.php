<?php

namespace App\Http\Controllers\Shadow\V1;

use App\Helpers\RestResponseFactory;
use App\Http\Controllers\Controller;
use App\Models\Factory\UserFactory;
use Illuminate\Http\Request;
use App\Services\Core\Zhima\ZhimaService;
use App\Models\Factory\ZhimaFactory;
use App\Helpers\RestUtils;
use App\Models\Chain\UserZhima\DoZhimaHandler;

/**
 * Class AuthController
 * @package App\Http\Controllers\V2
 * 登录&注册
 */
class ZhimaController extends Controller
{
    /**
     * 主页
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(Request $request)
    {
        $all = $request->all();
        $data['name'] = $all['name'];
        $data['idcard'] = $all['card'];
        $data['userId'] = UserFactory::getUserIdByIdCard($all['card']);
        $data['mobile'] = UserFactory::fetchMobile($data['userId']);
        $data['pay_type'] = 1;
        $re = ZhimaService::i()->query($data);
        return RestResponseFactory::ok(['res' => $re]);
    }

    public function getScore(Request $request)
    {
        $http = $request->all();
        // 获取Openid
        $zhima_str = ZhimaService::i()->getScoreOpenId($http['params'], $http['sign']);
        preg_match("/open_id=(\d*?)&/u", $zhima_str, $openId);
        preg_match("/state=(\d*?)&/u", $zhima_str, $info);
        $openId = $openId[1];
        $oldScore = ZhimaFactory::getOldScore($openId);
        $params = ZhimaService::i()->getZhimaCreditScore($openId);
        $idcard = $info[1];
        $userId = UserFactory::fetchUserIdByIdcard($idcard);
        $name = UserFactory::fetchRealNameByIdcard($idcard);
        $mobile = UserFactory::fetchMobile($userId);
        $params['userId'] = $userId; // userid
        $params['idcard'] = $idcard;
        $params['name'] = $name;
        $params['phone'] = $mobile;
        $params['identityType'] = 2;
        $params['score_old'] = $oldScore;
        $params['score_new'] = $params['score'];

        $model = new DoZhimaHandler($params);
        $res = $model->handleRequest();
        if (!$res)
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(9108), 9108);
        }

        return RestResponseFactory::ok();

    }
}