<?php

namespace App\Http\Controllers\V1;

use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\BanksFactory;
use App\Models\Factory\DeviceFactory;
use App\Strategies\BanksStrategy;
use Illuminate\Http\Request;

/**
 * Class BanksController
 * @package App\Http\Controllers\V1
 * 拥有信用卡的银行模块
 */
class BanksController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 热门银行
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
        //热门产品
        $hots = BanksFactory::fetchHotsByDeviceIds($data);
        //暂无产品
        if (empty($hots)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //银行logo数据转化
        $hots = BanksStrategy::getBankLogo($hots);

        return RestResponseFactory::ok($hots);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 可查进度银行
     */
    public static function fetchProgressBanks(Request $request)
    {
        $data['userId'] = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';
        //设备id
        $data['deviceId'] = $request->input('deviceId', '');
        //查询办卡进度产品
        $progressBanks = BanksFactory::fetchProgressBanks($data);
        //暂无产品
        if (empty($progressBanks)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //银行logo数据转化
        $progressBanks = BanksStrategy::getBankLogo($progressBanks);

        return RestResponseFactory::ok($progressBanks);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 银行列表 需要定位传deviceId
     */
    public function fetchHasCreditcardBanks(Request $request)
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
        //$data['is_remind'] = empty($data['deviceId']) ? 1 : 0;
        //所有含有信用卡的银行
        $banks = BanksFactory::fetchHasCreditcardBanks($data);
        //暂无银行列表
        if (!$banks) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($banks);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 立即激活银行  不进行定位
     */
    public function fetchActives()
    {
        $actives = BanksFactory::fetchActives();
        if (!$actives) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $actives = BanksStrategy::getBankLogo($actives);

        return RestResponseFactory::ok($actives);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 立即提额
     */
    public function fetchQuotas(Request $request)
    {
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);

        $quotas = BanksFactory::fetchQuotas($data);
        $pageCount = $quotas['pageCount'];
        if (!$quotas['list']) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //在线提额数据转化
        $quotas = BanksStrategy::getQuotas($quotas['list']);

        $datas['list'] = $quotas;
        $datas['pageCount'] = $pageCount;
        return RestResponseFactory::ok($datas);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 立即提额银行详情
     */
    public function fetchQuotaBankInfo(Request $request)
    {
        $bankId = $request->input('bankId');
        $info = BanksFactory::fetchBanksById($bankId);
        if (!$info) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $info = BanksStrategy::getQuotaBankInfo($info);

        return RestResponseFactory::ok($info);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 获取提醒银行列表
     */
    public function fetchBankUsages()
    {
        $bankUsage = BanksFactory::fetchBankUsages();
        //没有提醒银行
        if (!$bankUsage) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }

        return RestResponseFactory::ok($bankUsage);
    }


}