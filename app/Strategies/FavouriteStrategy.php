<?php

namespace App\Strategies;

use App\Helpers\DateUtils;
use App\Helpers\LinkUtils;
use App\Helpers\Utils;
use App\Models\ComModelFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * 公共策略
 *
 * @package App\Strategies
 */
class FavouriteStrategy extends AppStrategy
{
    /**
     * @param $productArr
     * 产品——产品收藏列表数据处理
     */
    public static function getCollectionLists($type, $product, $countPage)
    {
        foreach ($product as $key => $val) {
            $product[$key]['position_sort'] = $val['position_sort'];
            $product[$key]['star'] = $val['satisfaction'] . '';
            $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
            $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
            $product[$key]['success_count'] = DateUtils::ceilMoney($val['success_count']);
            $product[$key]['fast_time'] = ProductStrategy::fetchFastTime($val['value']);
            $product[$key]['productType'] = intval($type);
            $product[$key]['sign'] = 1;
        }
        $dataAll['list'] = $product;
        $dataAll['pageCount'] = $countPage;
        return $dataAll;
    }

    /**
     * 第二版 产品收藏列表数据处理
     * @param array $params
     * @return array
     */
    public static function getProductCollections($params = [])
    {
        $product = [];
        foreach ($params['products'] as $key => $val) {
            $product[$key]['platform_product_id'] = $val['platform_product_id'];
            $product[$key]['platform_id'] = $val['platform_id'];
            $product[$key]['platform_product_name'] = $val['platform_product_name'];
            $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
            $product[$key]['tag_name'] = $val['tag_name'];
            $successCount = bcadd($val['success_count'], 0);
            $todayTotalCount = bcadd($val['total_today_count'], 0);
            $product[$key]['success_count'] = DateUtils::ceilMoney($todayTotalCount);
            //今日申请总数
            $product[$key]['total_today_count'] = DateUtils::ceilMoney($todayTotalCount);
            $product[$key]['product_introduct'] = Utils::removeHTML($val['product_introduct']);
            //额度
            $product[$key]['interest_alg'] = $val['interest_alg'];
            $loan_min = DateUtils::formatIntToThous($val['loan_min']);
            $loan_max = DateUtils::formatIntToThous($val['loan_max']);
            $product[$key]['quota'] = $loan_min . '~' . $loan_max;
            //期限
            $period_min = ProductStrategy::formatDayToMonthByInterestalg($val['interest_alg'], $val['period_min']);
            $period_max = ProductStrategy::formatDayToMonthByInterestalg($val['interest_alg'], $val['period_max']);
            $product[$key]['term'] = $period_min . '~' . $period_max;

            $product[$key]['productType'] = 1;
            $product[$key]['sign'] = 1;

            //日、月利息
            $product[$key]['interest_rate'] = $val['min_rate'] . '%';
            //下款时间
            $loanSpeed = empty($val['value']) ? '3600' : $val['value'];
            $product[$key]['loan_speed'] = ProductStrategy::formatLoanSpeed($loanSpeed) . '小时';
            //是否是速贷优选产品
            $product[$key]['is_preference'] = isset($val['is_preference']) ? $val['is_preference'] : 0;
            //加密手机号
            $product[$key]['mobile'] = ProductStrategy::fetchEncryptMobile($params);
            //对接标识
            $product[$key]['type_nid'] = isset($val['type_nid']) ? strtolower($val['type_nid']) : '';
        }

        return $product;
    }

    /**
     * @param $newsArr
     * @param $pageCount
     * 资讯——资讯收藏列表数据处理
     */
    public static function getCollectionNewsLists($newsArr, $data)
    {
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;

        $newsData = [];
        foreach ($newsArr as $key => $val) {
            $newsData[$key]['id'] = $val['id'];
            $newsData[$key]['title'] = $val['title'];
            $newsData[$key]['cover_img'] = QiniuService::getInfoImgs($val['cover_img']);
            $newsData[$key]['create_time'] = DateUtils::formatDate($val['create_time']);
            $newsData[$key]['is_collection'] = 1;
            $newsData[$key]['footer_img_h5_link'] = empty($val['footer_img_h5_link']) ?
                LinkUtils::appLink($val['id']) : $val['footer_img_h5_link'];
            $newsData[$key]['footer_img_h5'] = $val['footer_img_h5_link'];
        }
        //分页
        $newsLists = DateUtils::pageInfo($newsData, $pageSize, $pageNum);
        return $newsLists ? $newsLists : [];
    }
}
