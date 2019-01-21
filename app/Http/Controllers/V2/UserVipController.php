<?php

namespace App\Http\Controllers\V2;

use App\Constants\PaymentConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Utils;
use App\Http\Controllers\Controller;
use App\Models\Factory\PaymentFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserOrderFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Services\AppService;
use App\Services\Core\Payment\PaymentService;
use App\Services\Core\Payment\YiBao\YiBaoService;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\PaymentStrategy;
use App\Strategies\UserBankCardStrategy;
use App\Strategies\UserVipStrategy;
use Illuminate\Http\Request;

use App\Helpers\RestResponseFactory;
use App\Helpers\UserAgent;
use App\Helpers\RestUtils;

/**
 * 会员相关
 * Class UserVipController
 * @package App\Http\Controllers\V2
 */
class UserVipController extends Controller
{
    /**
     * 会员中心
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function memberCenter(Request $request)
    {
        $params['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //终端类型
        $data['terminalType'] = $request->input('terminalType', '');

        $arr = UserVipStrategy::isUserVipAgain($params['userId'], $data['terminalType']);

        //客服电话
        $arr['phone'] = UserVipConstant::CONSUMER_HOTLINE;

        //特权专属
        $arr['privilege'] = UserVipStrategy::getVipPrivilege();

        return RestResponseFactory::ok($arr);
    }

}