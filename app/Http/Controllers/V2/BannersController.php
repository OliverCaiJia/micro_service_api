<?php

namespace App\Http\Controllers\V2;

use App\Constants\BannersConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\ComModelFactory;
use App\Models\Factory\BannersFactory;
use App\Strategies\BannerStrategy;
use Illuminate\Http\Request;

/**
 * Banners
 */
class BannersController extends Controller
{
    /**
     * 首页广告
     */
    public function fetchBanners(Request $request)
    {
        $channel_fr = $request->input('sd_plat_fr','channel_2');

        // 计算每个端口号的注册用户量 port_count
        $channel_fr = !empty($channel_fr) ? $channel_fr : 'channel_2';
        // 渠道流水添加
        ComModelFactory::createDeliveryLog($channel_fr);
        //总统计量添加
        ComModelFactory::channelVisitStatistics($channel_fr);

        //广告type_id
        $typeNid = BannersConstant::BANNER_TYPE_NEW_BANNER; //广告
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists    = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }
    /**
     * 首页分类专题
     * type_nid = 3  新分类专题
     */
    public function fetchSpecials()
    {
        //type_nid = 3 新分类专题
        $typeNid = BannersConstant::BANNER_CREDIT_CARD_TYPE_NEW_SPECIAL;
        //类别是否存在，大类别不存在即整类都不存在
        $status = 1; //显示
        $typeId = BannersFactory::fetchspecialsCategory($typeNid, $status);
        //重新查询产品数据
        $specials = BannersFactory::fetchCashBanners($typeId);

        //暂无数据
        if (empty($specials) || empty($typeId)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $cashData = BannerStrategy::getCashBanners($specials, $hotImg = '');

        return RestResponseFactory::ok($cashData);
    }

    /**
     * 第二版
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchSubjects()
    {
        $typeNid = BannersConstant::BANNER_CREDIT_CARD_TYPE_SECOND_EDITION_RECOMMEND;
        $status = 1; //存在
        $typeId = BannersFactory::fetchTypeId($typeNid);

        //获取速贷专题数据
        $subjects = BannersFactory::fetchSubjects($status, $typeId);
        //暂无数据
        if (empty($subjects)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //处理数据
        $subjects = BannerStrategy::getBanners($subjects);

        return RestResponseFactory::ok($subjects);
    }
}