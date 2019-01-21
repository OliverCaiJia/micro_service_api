<?php

namespace App\Strategies;

use App\Helpers\LinkUtils;
use App\Models\Factory\CreditcardBannersFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * Class CreditcardBannersStrategy
 * @package App\Strategies
 * 信用卡图片策略
 */
class CreditcardBannersStrategy extends AppStrategy
{
    /**
     * @param $banners
     * @return mixed
     * 信用卡轮播图片地址数据转化
     */
    public static function getBanners($banners)
    {
        foreach ($banners as $key => $val) {
            $banners[$key]['img_link'] = QiniuService::getImgs($val['img_link']);
        }
        return $banners;
    }

    /**
     * @param $banners
     * @return mixed
     * 特色精选图片转化
     */
    public static function getImageLink($banners)
    {
        foreach ($banners as $key => $val) {
            $banners[$key]['special_link'] = QiniuService::getImgs($val['special_link']);
        }
        return $banners;
    }

    /**
     * @param $images
     * @return mixed
     * 获取用途的名称
     */
    public static function getUsageTypeName($images)
    {
        foreach ($images as $key => $val) {
            $images[$key]['usage_name'] = CreditcardBannersFactory::fetchUsageTypeNameByTypeNid($val['usage_type_nid']);
            $images[$key]['img_link'] = QiniuService::getImgs($val['img_link']);
        }

        return $images;
    }
}