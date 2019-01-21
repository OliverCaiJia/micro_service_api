<?php

namespace App\Strategies;

use App\Helpers\LinkUtils;
use App\Helpers\RestUtils;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * 广告位策略
 *
 * Class UserStrategy
 * @package App\Strategies
 */
class BannerStrategy extends AppStrategy
{

    /**
     * @param $bannerList
     * @return mixed
     * 返回banner处理之后的图片
     */
    public static function getBanners($bannerList)
    {
        foreach ($bannerList as $key => $val) {
            $bannerList[$key]['src'] = QiniuService::getInfoImgs($val['src']);
            $newsId = stristr($val['app_url'], 'zixun');
            $newsId = substr($newsId, -2);
            $bannerList[$key]['footer_img_h5_link'] = !empty($newsId) ? LinkUtils::appLink($newsId) : '';
            $dbredirect = stristr($val['app_url'], 'dbredirect');
            //兑吧对接
            if ($dbredirect) {
                $bannerList[$key]['app_url'] = urldecode($val['app_url']);
            }

        }
        return $bannerList;
    }

    /**
     * @param $cashData
     * @return array
     * 返回首页分类专题&热门贷款
     */
    public static function getCashBanners($cashData, $hotImg)
    {
        foreach ($cashData as $key => $val) {
            $cashData[$key]['src'] = QiniuService::getInfoImgs($val['src']);
            $cashData[$key]['payback_type'] = empty($val['type_nid']) ? '' : $val['type_nid'];
        }
        $cashArr['list'] = $cashData ? $cashData : [];
        $cashArr['hot_img'] = QiniuService::getImgs($hotImg);
        return $cashArr;
    }

    /**
     * 首页分类专题 & 速贷推荐 数据处理
     * @param array $params
     * @return array
     */
    public static function getSpecialsAndRecommends($params = [])
    {
        $res = [];
        foreach ($params as $key => $val) {
            $res[$key]['id'] = $val['id'];
            $res[$key]['src'] = QiniuService::getInfoImgs($val['src']);
            $res[$key]['app_link'] = $val['app_link'];
            $res[$key]['h5_link'] = $val['h5_link'];
            $res[$key]['title'] = $val['title'];
        }

        return $res;
    }

}
