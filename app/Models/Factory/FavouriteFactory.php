<?php

namespace App\Models\Factory;

use App\Helpers\DateUtils;
use App\Models\AbsModelFactory;
use App\Models\Orm\FavouriteInfo;
use App\Models\Orm\FavouritePlatform;
use App\Models\Orm\News;
use App\Models\Orm\PlatformProduct;
use Illuminate\Support\Facades\DB;

/**
 * 渠道数据统计
 */
class FavouriteFactory extends AbsModelFactory
{
    /**
     * @param array $datas
     * @return bool
     * 产品——通过user_id && product_id 删除收藏字段
     */
    public static function deleteCollectionByUidAndPid($userId, $productId)
    {
        $del = FavouritePlatform::select()->where(['user_id' => $userId])
            ->where(['platform_product_id' => $productId])
            ->delete();

        return $del ? $del : false;
    }

    /**
     * @param array $datas
     * 产品——通过user_id && product_id 新增收藏字段
     */
    public static function createCollectionByUidAndPid($userId, $productId)
    {
        $create = FavouritePlatform::firstOrCreate(
            ['user_id' => $userId, 'platform_product_id' => $productId], [
            'user_id' => $userId,
            'platform_product_id' => $productId,
            'start_time' => date('Y-m-d H:i:s', time()),
        ]);
        $create->platform_product_id = intval($productId);
        $create->user_id = intval($userId);
        $create->start_time = date('Y-m-d H:i:s', time());
        return $create->save();

    }

    /**
     * @param $userId
     * @return array
     * 产品——获取用户收藏产品id  product_id
     */
    public static function fetchCollectionProductId($userId)
    {
        $productIdArr = FavouritePlatform::select(['platform_product_id'])
            ->where(['user_id' => $userId])
            ->orderBy('start_time', 'desc')
            ->pluck('platform_product_id')->toArray();
        return $productIdArr ? $productIdArr : [];
    }

    /**
     * @param $userId
     * 产品——获取收藏产品的数据
     */
    public static function fetchCollectionLists($data = [], $productIdArr = [])
    {
        $pageSize = isset($data['pageSize']) ? trim($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? trim($data['pageNum']) : 10;
        $key = $data['key'];
        $condition = implode(',', $productIdArr);

        //产品数据
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.position_sort',
                'p.composite_rate', 'p.loan_speed', 'p.experience', 'p.fast_time', 'pro.value', 'p.satisfaction'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $productIdArr);

        $query->when($condition, function ($query) use ($condition) {
            $query->orderByRaw(DB::raw("FIELD(`platform_product_id`, " . $condition . ')'));
        });

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $product = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $productArr['pageCount'] = $countPage;
        $productArr['list'] = $product ? $product : [];

        return $productArr ? $productArr : [];

    }

    /**
     * 第二版 收藏列表
     * @param array $params
     * @return array
     */
    public static function fetchProductCollections($params = [])
    {
        $pageSize = isset($params['pageSize']) ? intval($params['pageSize']) : 1;
        $pageNum = isset($params['pageNum']) ? intval($params['pageNum']) : 10;
        $key = $params['key'];
        $condition = implode(',', $params['productIdArr']);

        //产品数据
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $params['productIdArr']);

        $query->when($condition, function ($query) use ($condition) {
            $query->orderByRaw(DB::raw("FIELD(`platform_product_id`, " . $condition . ')'));
        });

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $product = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $productArr['pageCount'] = $countPage;
        $productArr['list'] = $product ? $product : [];

        return $productArr ? $productArr : [];

    }

    /**
     * @param $userId
     * 资讯——获取用户收藏资讯id
     */
    public static function fetchCollectionNewsId($data = [])
    {
        $newsIdData = FavouriteInfo::select(['news_id'])
            ->where(['user_id' => $data['userId']])
            ->orderBy('start_time', 'desc')
            ->pluck('news_id')->toArray();

        return $newsIdData ? $newsIdData : [];
    }

    /**
     * @param array $data
     * 资讯——收藏资讯列表
     */
    public static function fetchCollectionNewsLists($data = [], $newsIdArr = [])
    {
        $newsArr = [];
        foreach ($newsIdArr as $key => $val) {
            $newsObj = News::select(['n.id', 'n.title', 'n.cover_img', 'n.visit_count',
                'n.news_theme_id', 'n.create_time', 'n.footer_img_h5_link', 'n.status'])
                ->from('sd_news as n')
                ->join('sd_news_theme as nt', 'n.news_theme_id', '=', 'nt.id')
                ->where(['n.id' => $val])
                ->where(['n.status' => 0])
                ->where('nt.status', '<>', 9)
                ->first();
            $newsArr[$key] = $newsObj ? $newsObj->toArray() : [];
        }
        $newsArr = DateUtils::formatArray($newsArr);
        return $newsArr;
    }

    /**
     * @param $userId
     * @param $newsId
     * 资讯——收藏资讯
     */
    public static function createClooectionNewsByUidAndPid($userId, $newsId)
    {
        $create = FavouriteInfo::firstOrCreate(['user_id' => $userId, 'news_id' => $newsId], [
            'user_id' => $userId,
            'news_id' => $newsId,
            'start_time' => date('Y-m-d H:i:s', time()),
        ]);
        $create->news_id = intval($newsId);
        $create->user_id = intval($userId);
        $create->start_time = date('Y-m-d H:i:s', time());
        return $create->save();
    }

    /**
     * @param $userId
     * @param $newsId
     * 资讯——通过user_id && news_id 删除收藏字段
     */
    public static function deleteCollectionNewsByUidAndPid($userId, $newsId)
    {
        $del = FavouriteInfo::select()->where(['user_id' => $userId])
            ->where(['news_id' => $newsId])
            ->delete();

        return $del ? $del : false;
    }



    ///////////////////////////////////////////////////////////////////////////////////////////////////
    //是否收藏产品
    public static function collectionProducts($userId, $productId)
    {
        $sign = 0;
        if (!empty($userId)) {
            $collectionPro = FavouritePlatform::where(['user_id' => $userId, 'platform_product_id' => $productId])
                ->first();
            if (!empty($collectionPro)) {
                $sign = 1;
            }
        }
        return $sign;
    }

}
