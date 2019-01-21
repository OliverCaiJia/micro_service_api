<?php

namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Apply\DoApplyHandler;
use App\Models\Factory\PlatformFactory;
use App\Models\Factory\UserFactory;
use App\Strategies\OauthStrategy;
use Illuminate\Http\Request;

class OauthController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * 产品详情——点击借款
     */
    public function fetchLoanmoney(Request $request)
    {
        $data = $request->all();
        $userId = $request->user()->sd_user_id;
        $data['userId'] = $userId;

        //获取平台网址
        $platformWebsite = PlatformFactory::fetchProductWebsite($data['productId']);
        if (empty($platformWebsite)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        //获取用户手机号
        $user = UserFactory::fetchUserById($userId);
        //数据处理
        $data = OauthStrategy::getOauthProductDatas($data, $user, $platformWebsite);

        //申请借款责任链
        $re = new DoApplyHandler($data);
        $urlArr = $re->handleRequest();

        return RestResponseFactory::ok($urlArr);
    }


}