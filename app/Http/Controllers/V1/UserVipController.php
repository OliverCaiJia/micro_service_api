<?php

namespace App\Http\Controllers\V1;

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

        //贷款产品
        $loanVipArr = ProductFactory::fetchProductVipIds(UserVipFactory::getVipTypeId());
        $data['productIds'] = $loanVipArr;
        $data['loanVipCount'] = ProductFactory::fetchProductCounts($data);
        $loanComArr = ProductFactory::fetchProductVipIds(UserVipFactory::getCommonTypeId());
        $data['productIds'] = $loanComArr;
        $data['loanCommonCount'] = ProductFactory::fetchProductCounts($data);

        //下款率
        $data['downCommonRate'] = UserVipConstant::MEMBER_COMMON_DOWN_RATE;
        $data['downVipRate'] = UserVipConstant::MEMBER_VIP_DOWN_RATE;

        //会员动态
        $data['memberActivity'] = UserVipStrategy::getMemberActivityInfo();

        //价格
        $data['totalPrice'] = '￥' . UserVipFactory::getVipAmount() . '/年';
        $data['totalNoPrice'] = '￥' . UserVipConstant::MEMBER_PRICE . '/年';
        $data['totalPriceTime'] = UserVipStrategy::isUserVip($params['userId']);

        //客服电话
        $data['phone'] = UserVipConstant::CONSUMER_HOTLINE;

        return RestResponseFactory::ok($data);
    }

    /**
     * 可用银行卡列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBankList(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //根据id显示查询标识
        $data['userBankId'] = $request->input('userBankId', 0);

        //该用户是否含有银行卡
        $bankCount = UserBankCardFactory::fetchUserBanksCount($data['userId']);
        //有银行修改状态
        if ($bankCount != 0) {
            //查询没有上次支付状态则设置一个默认支付状态
            $cardPay = UserBankCardFactory::fetchCardLastPayById($data['userId']);
            if (!$cardPay) {
                //如果有储蓄卡则设置默认支付卡，没有储蓄卡则设置最近的一张信用卡为支付卡
                $cardLastPay = UserBankCardFactory::updateCardLastPayStatus($data['userId']);
                if (!$cardLastPay) {
                    return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
                }
            }
        }

        //获取用户的银行列表
        $list = UserBankCardFactory::getUsedCardList($data);
        $pageCount = $list['pageCount'];
        //暂无数据
        if (empty($list['list'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $data['list'] = $list['list'];
        $reList['list'] = UserBankCardStrategy::getBackBankInfo($data);
        $reList['pageCount'] = $pageCount;

        return RestResponseFactory::ok($reList);
    }

    /**
     * 会员中心-普通用户
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function memberHome(Request $request)
    {
        //过期年限
        $data['expired'] = UserVipStrategy::getVipYeer();
        $data['member_price'] = UserVipFactory::getVipAmount();
        $data['telephone_num'] = UserVipConstant::CONSUMER_HOTLINE;

        return RestResponseFactory::ok($data);
    }

}