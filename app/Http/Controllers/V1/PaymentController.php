<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserReportConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\Order\VipOrder\DoVipOrderLogicHandler;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserOrderFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Services\AppService;
use App\Services\Core\Payment\PaymentService;
use App\Strategies\PaymentStrategy;
use App\Strategies\UserBankCardStrategy;
use App\Strategies\UserReportStrategy;
use App\Strategies\UserVipStrategy;
use Illuminate\Http\Request;

/**
 * 支付
 *
 * Class PaymentController
 * @package App\Http\Controllers\V1
 */
class PaymentController extends Controller
{
    /**
     * 确认页面,商品信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOrderInfo(Request $request)
    {
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $orderType = $request->input('type', '');
        $id = $request->input('userBankId', '');

        $data = [];
        //存在银行id，按银行id进行查询
        if ($id) {
            $reqData['userId'] = $userId;
            $reqData['id'] = $id;
            $data = UserBankCardFactory::fetchUserBankInfoById($reqData);
        } else {
            //查询没有上次支付状态则设置一个默认支付状态
            $data = UserBankCardFactory::fetchCardLastPayById($userId);
        }
        //没有上次支付则使用默认或是最近添加银行卡
        if (!$data) {
            //如果有储蓄卡则设置默认支付卡，没有储蓄卡则设置最近的一张信用卡为支付卡
            $cardLastPay = UserBankCardFactory::updateCardLastPayStatus($userId);
            //再次查询新设置的支付卡
            $data = UserBankCardFactory::fetchCardLastPayById($userId);
        }

        //数据处理
        $data = UserBankCardStrategy::getPaymentBank($data);
        $data['orderType'] = $orderType;
        //根据类型获取id
        //是否是会员
        $userVipType = UserVipFactory::fetchUserVipToTypeByUserId($userId);
        //根据vipType 获取vip_nid
        $data['vipNid'] = UserVipFactory::fetchVipTypeById($userVipType);

        //会员信息
        $data['message'] = UserVipFactory::getVIPInfo($userId, $userVipType);
        //数据处理
        $reList = PaymentStrategy::getDiffTypeInfo($data);
        $reList['default_card'] = $data;

        return RestResponseFactory::ok($reList);
    }

    /**
     * 订单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOrder(Request $request)
    {
        $order['pay_type'] = (int)$request->input('payType', 3);
        $order['terminal_id'] = $request->input('terminalId', '');
        $order['bankcard_id'] = (int)$request->input('bankcardId', 0); //user_banks表的ID
        $order['shadow_nid'] = $request->input('shadowNid', '');
        $order['user_id'] = (string)$request->user()->sd_user_id;
        $order['type'] = $request->input('type');

        $result = PaymentStrategy::getDiffOrderTypeChain($order);
        if (isset($result['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $result['error'], $result['code']);
        }

        return RestResponseFactory::ok($result);
    }

}