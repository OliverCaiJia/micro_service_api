<?php

namespace App\Http\Controllers\V1;

use App\Constants\CreditcardConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Factory\CreditcardBannersFactory;
use App\Models\Factory\CreditcardTypeFactory;
use App\Strategies\CreditcardBannersStrategy;
use Illuminate\Http\Request;

/**
 * Class CreditcardBannersController
 * @package App\Http\Controllers\V1
 * 信用卡图片模块
 */
class CreditcardBannersController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @typeNid 轮播图片类型唯一标识
     * progress_query 进度查询，
     * 信用卡轮播图片
     */
    public function fetchBankBanners(Request $request)
    {
        //轮播图片类型唯一标识
        $typeNid = $request->input('typeNid');
        //类型表
        $bannerTypeId = CreditcardBannersFactory::fetchBankBannerTypeId($typeNid);
        //轮播图片
        $banners = CreditcardBannersFactory::fetchBankBanners($bannerTypeId);
        //暂无图片数据
        if (!$bannerTypeId || !$banners) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //图片地址处理
        $banners = CreditcardBannersStrategy::getBanners($banners);

        return RestResponseFactory::ok($banners);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 特色精选图片
     */
    public function fetchSpecialImages()
    {
        //特色精选唯一标识
        $typeNid = CreditcardConstant::SPECIAL;
        $specialId = CreditcardTypeFactory::fetchSpecialIdByTypeNid($typeNid);
        $specialImages = CreditcardTypeFactory::fetchSpecialImages($specialId);
        //暂无特色精选图片
        if (!$specialId || !$specialImages) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $specialImages = CreditcardBannersStrategy::getImageLink($specialImages);
        return RestResponseFactory::ok($specialImages);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 用途卡片
     */
    public function fetchUsageImages()
    {
        $typeNid = CreditcardConstant::IMAGE_USAGE;
        $imageId = CreditcardTypeFactory::fetchImageIdByTypeNid($typeNid);
        $images = CreditcardTypeFactory::fetchUsageImages($imageId);

        //暂无用途卡片图片
        if (!$imageId || !$images) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        $images = CreditcardBannersStrategy::getUsageTypeName($images);

        return RestResponseFactory::ok($images);
    }
}