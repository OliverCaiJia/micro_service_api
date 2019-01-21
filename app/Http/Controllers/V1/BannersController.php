<?php

namespace App\Http\Controllers\V1;

use App\Constants\BannersConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\BannersFactory;
use App\Models\Factory\NewsFactory;
use App\Models\Factory\PushFactory;
use App\Strategies\BannerStrategy;
use App\Strategies\NewStrategy;
use App\Strategies\PushStrategy;
use Illuminate\Http\Request;
use App\Models\ComModelFactory;

/**
 * Default controller for the `api` module
 */
class BannersController extends Controller
{

    /**
     * 首页广告
     */
    public function banners(Request $request)
    {
        $channel_fr = $request->input('sd_plat_fr', 'channel_2');

        // 计算每个端口号的注册用户量 port_count
        $channel_fr = !empty($channel_fr) ? $channel_fr : 'channel_2';
        // 渠道流水添加
        ComModelFactory::createDeliveryLog($channel_fr);
        //总统计量添加
        ComModelFactory::channelVisitStatistics($channel_fr);

        //广告type_id
        $typeNid = BannersConstant::BANNER_TYPE_BANNER; //广告
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }

    /**
     * 首页分类专题
     * ad_num = 1 分类专题
     */
    public function fetchSpecials(Request $request)
    {
        $typeId = $request->input('adNum');

        if ($typeId == 1) {
            $typeNid = BannersConstant::BANNER_CREDIT_CARD_TYPE_SPECIAL;
        } elseif ($typeId == 2) {
            $typeNid = BannersConstant::BANNER_CREDIT_CARD_TYPE_HOT_LOAN;
        } else {
            $typeNid = '';
        }

        //类别是否存在，大类别不存在即整类都不存在
        $status = 1; //显示
        $realTypeId = BannersFactory::fetchspecialsCategory($typeNid, $status);
        $adNum = $realTypeId;

        if ($adNum == 2) {
            $cashData = BannersFactory::fetchCashBannersNoStatus($adNum);
            //判断产品是否下线 下线修改状态
            BannersFactory::updateBannerCreditCardStatus($cashData);

        }
        //重新查询产品数据
        $cashData = BannersFactory::fetchCashBanners($adNum);

        //热门贷款节日图片
        $hotImg = BannersFactory::fetchBannerConfig();
        //数据处理
        $cashData = BannerStrategy::getCashBanners($cashData, $hotImg);

        return RestResponseFactory::ok($cashData);
    }

    /**
     * ad_num = 2 热门贷款[速贷推荐]
     */
    public function fetchRecommends()
    {
        $adNum = 2;
        $cashData = BannersFactory::fetchCashBanners($adNum);
        //判断产品是否下线 下线修改状态
        BannersFactory::updateBannerCreditCardStatus($cashData);

        //重新查询产品数据
        $cashDataNew = BannersFactory::fetchCashBanners($adNum);
        //热门贷款节日图片
        $hotImg = BannersFactory::fetchBannerConfig();
        //数据处理
        $cashDataNew = BannerStrategy::getCashBanners($cashDataNew, $hotImg);

        return RestResponseFactory::ok($cashDataNew);
    }

    /**
     * @param Request $request
     * @return mixed
     * banner 中 newsid 所对应的资讯详情 [App使用]
     */
    public function fetchNewinfoById(Request $request)
    {
        $newsId = $request->input('newsId', '');
        $userId = isset($request->user()->sd_user_id) ? $request->user()->sd_user_id : '';

        //资讯详情
        $detailLists = NewsFactory::fetchDetails($newsId);
        if (empty($detailLists)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
        }

        //点击量统计
        NewsFactory::fetchClicks($newsId);

        //数据处理
        $detailLists = NewStrategy::getBannerNewsById($detailLists);

        //收藏
        if (!empty($userId)) {
            $detailLists['sign'] = NewsFactory::collectionOne($newsId, $userId);
        }

        return RestResponseFactory::ok($detailLists);
    }

    /**
     * 启动页广告
     */
    public function launchAdvertisement()
    {
        //启动页的位置
        $position = 3;
        //查询需要推送的信息
        $push = PushFactory::fetchPopup($position);
        if (empty($push)) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500); //暂无数据
        }
        //执行次数叠加
        PushFactory::updateDoCounts($push['id']);
        $pushArr = PushStrategy::getPopup($push);
        return RestResponseFactory::ok($pushArr);
    }

    /**
     * 首页速贷推荐  跳转规则与banner一致
     */
    public function fetchSubjects()
    {
        $typeNid = BannersConstant::BANNER_TYPE_RECOMMEND; // 代表速贷推荐
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

    /**
     * 账单导入广告图片地址
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBillBanners()
    {
        $typeNid = BannersConstant::BANNER_BILL_IMPORT; // 代表账单导入
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

    /**
     * 会员中心广告
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchVipCenterBanner()
    {
        //会员中心广告type_id
        $typeNid = BannersConstant::BANNER_TYPE_VIP_CENTER;
        $typeId = BannersFactory::fetchTypeId($typeNid);

        $bannerLists = BannersFactory::fetchBanners($typeId);
        $resLists    = BannerStrategy::getBanners($bannerLists);

        return RestResponseFactory::ok($resLists);
    }
}
