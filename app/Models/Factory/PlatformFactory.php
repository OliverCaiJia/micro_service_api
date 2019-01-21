<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\Platform;
use App\Models\Orm\PlatformLoan;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\PlatformProductPosition;

/**
 * 渠道数据统计
 */
class PlatformFactory extends AbsModelFactory
{
    /**
     * @param $platformId
     * 获取平台网址
     */
    public static function fetchWebsite($platformId)
    {
        $websiteObj = Platform::select(['h5_register_link', 'official_website', 'anddroid_download',
            'apple_download', 'platform_name'])
            ->where(['platform_id' => $platformId])
            ->first();
        return $websiteObj ? $websiteObj->toArray() : [];
    }

    /**
     * @param $productId
     * @return array
     * 立即申请产品地址
     */
    public static function fetchProductWebsite($productId)
    {
        $products = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.platform_product_id' => $productId])
            ->select(['p.platform_product_name', 'p.platform_id', 'p.product_h5_url', 'p.official_website as product_official_website', 'p.apple_download as product_apple_download', 'p.android_download as product_android_download', 'p.channel_status as product_channel_status', 'p.type_nid as product_type_nid'])
            ->addSelect(['pf.h5_register_link', 'pf.official_website', 'pf.anddroid_download',
                'pf.apple_download', 'pf.platform_name', 'pf.type_nid', 'pf.channel_status'])
            ->first();

        return $products ? $products->toArray() : [];
    }

    /** 马甲产品url
     * @param array $data
     * @return string
     */
    public static function fetchShadowProductUrl($data = [])
    {
        $position = PlatformProductPosition::where('product_id', $data['productId'])->first();
        return $position ? $position->url : '';
    }

    /** 获取产品url
     * @param array $data
     * @return mixed|string
     */
    public static function fetchProductUrl($data = [])
    {
        $url = '';
        $product = PlatformProduct::where('platform_product_id', $data['productId'])->where('platform_id', $data['platformId'])->first();
        if (!empty($product)) {
            if (!empty($product->product_h5_url)) {
                $url = $product->product_h5_url;
            } elseif(!empty($product->official_website)) {
                $url = $product->official_website;
            }
        }

        return $url;
    }

    /**
     * @param $platformId
     * @param $userId
     * @return bool
     * 用户借款记录表数据统计
     */
    public static function createPlatgormLoanLog($platformId, $userId)
    {
        $loanMoneyModel = new PlatformLoan();
        $loanMoneyModel->platform_id = $platformId;
        $loanMoneyModel->user_id = $userId;
        $loanMoneyModel->create_date = date('Y-m-d H:i:s', time());
        return $loanMoneyModel->save();
    }

    /**
     * @param $platformId
     * @return mixed
     * 单个平台点击立即申请数据统计
     */
    public static function updatePlatformClick($platformId)
    {
        $platformObj = Platform::select()->where(['platform_id' => $platformId])
            ->first();
        $platformObj->increment('use_count', 1);

        return $platformObj->save();
    }

    /**
     * @param $platformId
     * @return string
     * 平台H5注册链接
     */
    public static function fetchPlatformUrl($platformId)
    {
        $platformUrl = Platform::select(['h5_register_link'])
            ->where(['platform_id' => $platformId])
            ->first();
        return $platformUrl ? $platformUrl->h5_register_link : '';
    }
}
