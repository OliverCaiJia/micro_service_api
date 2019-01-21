<?php

namespace App\Http\Controllers\V1;

use App\Constants\CreditcardConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\BanksFactory;
use App\Models\Factory\CreditcardFactory;
use App\Models\Factory\CreditcardTypeFactory;
use App\Models\Factory\DeviceFactory;
use App\Strategies\BanksStrategy;
use App\Strategies\CreditcardStrategy;
use App\Strategies\CreditcardTypeStrategy;
use Illuminate\Http\Request;

/**
 * Class CreditcardController
 * @package App\Http\Controllers\V1
 * 信用卡模块
 */
class CreditcardController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 信用卡搜索热词
     */
    public function fetchHots(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //设备id
        $data['deviceId'] = $request->input('deviceId', '');
        //城市id
        $areas = DeviceFactory::fetchCityByDeviceIdAndUserId($data);
        //城市id不为0时：根据城市id筛选出符合的银行id
        $data['deviceBankIds'] = BanksFactory::fetchDeviceBankIdsByDeviceId($areas['area_id']);
        //所有银行id
        $data['bankIds'] = BanksFactory::fetchBankIds();
        //有定位的所有银行id
        $data['cityBankIds'] = BanksFactory::fetchCityBankIds();
        //搜索热词
        $hots = CreditcardFactory::fetchHots($data);
        if (!$hots) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $hots = CreditcardStrategy::getHots($hots);
        return RestResponseFactory::ok($hots);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 信用卡搜索  与热词有关的搜索
     */
    public function fetchSearches(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //设备id
        $data['deviceId'] = $request->input('deviceId', '');
        $data['searchName'] = $request->input('searchName', '');
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //城市id
        $areas = DeviceFactory::fetchCityByDeviceIdAndUserId($data);
        //城市id不为0时：根据城市id筛选出符合的银行id
        $data['deviceBankIds'] = BanksFactory::fetchDeviceBankIdsByDeviceId($areas['area_id']);
        //所有银行id
        $data['bankIds'] = BanksFactory::fetchBankIds();
        //有定位的所有银行id
        $data['cityBankIds'] = BanksFactory::fetchCityBankIds();
        //搜索内容
        $searches = CreditcardFactory::fetchSearches($data);
        $pageCount = $searches['pageCount'];
        if (!$searches['list']) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据转化
        $searches = CreditcardStrategy::getSearches($searches['list']);
        //信用卡搜索流水
        $log = CreditcardFactory::createSearchLog($data);

        $datas['list'] = $searches;
        $datas['pageCount'] = $pageCount;

        return RestResponseFactory::ok($datas);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 信用卡筛选列表表头
     */
    public function fetchSelectTitles(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //设备id
        $data['deviceId'] = $request->input('deviceId', '');
        //城市id
        $areas = DeviceFactory::fetchCityByDeviceIdAndUserId($data);
        //城市id不为0时：根据城市id筛选出符合的银行id
        $data['deviceBankIds'] = BanksFactory::fetchDeviceBankIdsByDeviceId($areas['area_id']);
        //所有银行id
        $data['bankIds'] = BanksFactory::fetchBankIds();
        //有定位的所有银行id
        $data['cityBankIds'] = BanksFactory::fetchCityBankIds();

        //不需要提醒
        $data['is_remind'] = 0;
        //所有含有信用卡的银行
        $banks[] = ['id' => 0, 'bank_short_name' => '银行'];
        $bankLists = BanksFactory::fetchHasCreditcardBanks($data);
        $select['banks'] = BanksStrategy::getHasCreditcardBanks($banks, $bankLists);
        //用途
        $usage[] = ['id' => 0, 'name' => '用途', 'type_nid' => ''];
        $usageLists = CreditcardTypeFactory::fetchUsageType();
        $select['usage'] = CreditcardTypeStrategy::getUsageType($usage, $usageLists);
        //等级
        $select['degree'] = CreditcardConstant::SELECT_DEGREE;
        //年费
        $fee[] = ['id' => 0, 'name' => '年费', 'type_nid' => ''];
        $feeLists = CreditcardTypeFactory::fetchFeeType();
        $select['fee'] = CreditcardTypeStrategy::getUsageType($fee, $feeLists);

        return RestResponseFactory::ok($select);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 信用卡筛选
     */
    public function fetchCreditCardSearches(Request $request)
    {
        //所有参数
        $data = $request->all();
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //设备id
        $data['deviceId'] = $request->input('deviceId', '');
        //城市id
        $areas = DeviceFactory::fetchCityByDeviceIdAndUserId($data);
        //城市id不为0时：根据城市id筛选出符合的银行id
        $data['deviceBankIds'] = BanksFactory::fetchDeviceBankIdsByDeviceId($areas['area_id']);
        //所有银行id
        $data['bankIds'] = BanksFactory::fetchBankIds();
        //有定位的所有银行id
        $data['cityBankIds'] = BanksFactory::fetchCityBankIds();

        //用途筛选
        $usageId = CreditcardTypeFactory::fetchUsageIdByTypeNid($data['usageTypeNid']);
        //用途筛选对应的信用卡id
        $data['usageCreditcardId'] = CreditcardTypeFactory::fetchCreditcardIdByUsageId($usageId);

        //等级筛选
        $data['degreeCreditcardId'] = CreditcardTypeFactory::fetchCreditcardIdByDegreeId($data['degree']);

        //费率筛选
        $data['feeId'] = CreditcardTypeFactory::fetchFeeIdByTypeNid($data['feeTypeNid']);

        //信用卡筛选
        $creditcards = CreditcardFactory::fetchCreditCardSearches($data);
        $pageCount = $creditcards['pageCount'];

        //银行信息
        $banks = BanksFactory::fetchBanksById($data['bankId']);
        $banks = empty($banks) ? [] : BanksStrategy::getBanksById($banks);

        if (!$creditcards['list'] && !$banks) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据转化
        $creditcards = CreditcardStrategy::getSearches($creditcards['list']);

        $searches['banks'] = !empty($banks) ? $banks : RestUtils::getStdObj();
        $searches['list'] = $creditcards;
        $searches['pageCount'] = $pageCount;

        return RestResponseFactory::ok($searches);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 特色精选信用卡列表
     */
    public function fetchSpecials(Request $request)
    {
        //所有参数
        $data = $request->all();
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //设备id
        $data['deviceId'] = $request->input('deviceId', '');
        //城市id
        $areas = DeviceFactory::fetchCityByDeviceIdAndUserId($data);
        //城市id不为0时：根据城市id筛选出符合的银行id
        $data['deviceBankIds'] = BanksFactory::fetchDeviceBankIdsByDeviceId($areas['area_id']);
        //所有银行id
        $data['bankIds'] = BanksFactory::fetchBankIds();
        //有定位的所有银行id
        $data['cityBankIds'] = BanksFactory::fetchCityBankIds();

        //特色精选信用卡产品
        $specials = CreditcardFactory::fetchSpecials($data);
        $pageCount = $specials['pageCount'];

        if (!$specials['list']) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据转化
        $specials = CreditcardStrategy::getSearches($specials['list']);

        $searches['list'] = $specials;
        $searches['pageCount'] = $pageCount;
        return RestResponseFactory::ok($searches);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 办卡头条
     */
    public function fetchHeadlines()
    {
        $headlines = CreditcardConstant::HEAD_LINES;

        return RestResponseFactory::ok($headlines);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 办卡有礼对应信用卡
     */
    public function fetchSpecialGifts(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        $gifts = CreditcardFactory::fetchSpecialGifts($data);
        $pageCount = $gifts['pageCount'];
        //暂无产品
        if (!$gifts['list']) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //图片处理
        $gifts = CreditcardStrategy::getGiftsImgs($gifts['list']);

        $datas['list'] = $gifts;
        $datas['pageCount'] = $pageCount;
        return RestResponseFactory::ok($datas);
    }

    /**
     * 首页推荐 热门信用卡 限制两个
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchHomeSpecials(Request $request)
    {
        //所有参数
        $data = $request->all();
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //设备id
        $data['deviceId'] = $request->input('deviceId', '');
        //城市id
        $areas = DeviceFactory::fetchCityByDeviceIdAndUserId($data);
        //城市id不为0时：根据城市id筛选出符合的银行id
        $data['deviceBankIds'] = BanksFactory::fetchDeviceBankIdsByDeviceId($areas['area_id']);
        //所有银行id
        $data['bankIds'] = BanksFactory::fetchBankIds();
        //有定位的所有银行id
        $data['cityBankIds'] = BanksFactory::fetchCityBankIds();

        //特色精选信用卡产品
        $data['pageNum'] = 2;
        $specials = CreditcardFactory::fetchHomeSpecials($data);

        if (!$specials['list']) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据转化
        $specials = CreditcardStrategy::getSearches($specials['list']);

        return RestResponseFactory::ok($specials);
    }

    /**
     * 信用卡模块 —— 取现地址
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCashLink()
    {
        $cashLink = CreditcardConstant::CREDIT_CARD_CASH_LINK;

        $data['cash_link'] = $cashLink;
        return RestResponseFactory::ok($data);
    }
}