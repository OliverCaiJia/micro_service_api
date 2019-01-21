<?php

namespace App\Http\Controllers\Oneloan\V1;

use App\Constants\SpreadConstant;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Oneloan\Basic\DoBasicHandler;
use App\Models\Chain\Oneloan\Full\DoFullHandler;
use App\Models\Factory\CacheFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserSpreadFactory;
use App\Services\Core\Oneloan\OneloanService;
use App\Strategies\SpreadStrategy;
use Illuminate\Http\Request;
use App\Services\Core\Oneloan\Hengchang\Hengyidai\HengyidaiService;

/**
 * 一键贷推广
 * Class UserSpreadController
 * @package App\Http\Controllers\Oneloan\V2
 */
class UserSpreadController extends Controller
{
    /**
     * 基础信息页推送产品
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrUpdateBasic(Request $request)
    {
        //基础信息数据
        $data = $request->all();
        //用户信息
        $data['user_id'] = $request->user()->sd_user_id;
        $data['mobile'] = $request->user()->mobile;

        //SLogger::getStream()->info('basicForm', ['data' => $data]);

        $response = new DoBasicHandler($data);
        $res = $response->handleRequest();
        //请刷新重试
        if (!$res) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());

    }

    /**
     * 修改速贷之家一键贷用户填写完整信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrUpdateFullInfo(Request $request)
    {
        //接收数据
        $data = $request->all();

        //用户id
//        $data['user_id'] = $request->user()->sd_user_id;
//        $data['mobile'] = $request->user()->mobile;
        //生日格式处理 19900101 => 1990-01-01
        $data['birthday'] = DateUtils::formatBirthdayToYmd($data['birthday']);

        // 获取真实的身份证号/姓名/性别/生日
        $info = UserIdentityFactory::fetchUserRealInfo($data['user_id']);
        //不存在认证信息 获取用户所填信息
        if (!empty($info)) {
            $data['certificate_no'] = $info['certificate_no'];
            $data['name'] = $info['name'];
            $data['sex'] = $info['sex'];
            $data['birthday'] = DateUtils::formatBirthdayByStrto($info['birthday']);
        }

        $response = new DoFullHandler($data);
        $res = $response->handleRequest();
        //SLogger::getStream()->info('full匹配',['data'=>$res]);
        //请刷新重试
        if (!$res) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());

    }

}