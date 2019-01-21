<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\Banner;
use App\Models\Orm\BannerConfig;
use App\Models\Orm\BannerType;
use App\Models\Orm\CreditCardBanner;
use App\Models\Orm\CreditCardBannerType;
use App\Services\Core\Store\Qiniu\QiniuService;

class BannersFactory extends AbsModelFactory
{

    /**
     * @param $typeNid
     * @return string
     * 广告分类id
     */
    public static function fetchTypeId($typeNid)
    {
        $typeId = BannerType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $typeId ? $typeId->id : '';
    }

    /**
     * 获取首页banber数据
     * @param $typeId
     * @return array
     */
    public static function fetchBanners($typeId)
    {
        //type_id 1 广告，status 1 存在
        $time = date('Y-m-d H:i:s', time());
        $bannerList = Banner::where('endtime', '>', $time)
            ->where(['status' => 1, 'type_id' => $typeId])
            ->orderBy('position')
            ->limit(5)
            ->select('src', 'app_url', 'h5_link', 'name')
            ->get()->toArray();
        return $bannerList ? $bannerList : [];
    }

    /**
     * 热门贷款节日图片
     */
    public static function fetchBannerConfig()
    {
        $config = BannerConfig::select(['src'])
            ->where(['position' => 2, 'status' => 1])
            ->first();
        return $config ? $config->src : '';
    }

    /**
     * @param $adNum
     * @return array
     * 获取首页分类专题&热门贷款数据  不区分上下线
     */
    public static function fetchCashBannersNoStatus($adNum)
    {
        $cashData = CreditCardBanner::where(['type_id' => $adNum])
            ->orderBy('position', 'asc')
            ->select(['src', 'app_link', 'h5_link', 'title', 'id'])
            ->get();

        return $cashData ? $cashData->toArray() : [];
    }

    /**
     * @param $param
     * @return mixed
     * 获取首页分类专题&热门贷款数据
     */
    public static function fetchCashBanners($adNum)
    {
        $cashData = CreditCardBanner::where(['type_id' => $adNum])
            ->where('status', '<>', 9)
            ->where(['ad_status' => 0])
            ->orderBy('position', 'asc')
            ->select(['id', 'type_nid','src', 'app_link', 'h5_link', 'title'])
            ->get();

        return $cashData ? $cashData->toArray() : [];
    }

    /**
     * @param $cashData
     * 判断产品是否下线 下线修改状态
     */
    public static function updateBannerCreditCardStatus($cashData)
    {
        if (empty($cashData)) {
            return false;
        }

        foreach ($cashData as $key => $value) {
            $product = ProductFactory::productOne($value['app_link']);
            if (empty($product)) {
                CreditCardBanner::where(['id' => $value['id'], 'ad_status' => 0])->update(['ad_status' => 1]);
            }
        }

        return true;
    }

    /**
     * @param $typeId
     * @param $status
     * @return array
     * 分类专题图片类型是否存在
     */
    public static function fetchspecialsCategory($typeNid, $status)
    {
        $category = CreditCardBannerType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => $status])
            ->first();

        return $category ? $category->id : '';
    }

    /**
     * @param $typeNid
     * @param $status
     * @return string
     * 广告图片类型是否存在
     */
    public static function fetchBannersCategory($typeNid, $status)
    {
        $category = BannerType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => $status])
            ->first();

        return $category ? $category->id : '';
    }

    /**
     * @param $status
     * @param $typeId
     * @return array
     * 首页速贷专题  跳转规则与banner一致
     */
    public static function fetchSubjects($status, $typeId)
    {
        $time = date('Y-m-d H:i:s', time());
        $subjects = Banner::where(['status' => $status, 'type_id' => $typeId])
            ->where('endtime', '>', $time)
            ->orderBy('position')
            ->select('src', 'app_url', 'h5_link', 'name')
            ->get()->toArray();
        return $subjects ? $subjects : [];
    }


}
