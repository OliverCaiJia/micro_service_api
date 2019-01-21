<?php

namespace App\Models\Factory;

use App\Constants\ProductConstant;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\PlatformProductSearchFeedback;
use App\Models\Orm\PlatformProductSearchLog;
use App\Helpers\UserAgent;

/**
 * Class ProductSearchFactory
 * @package App\Models\Factory
 * 产品搜索
 */
class ProductSearchFactory extends AbsModelFactory
{
    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * @param $limit
     * @return array
     * 排名前16的产品名称
     */
    public static function fetchHots($limit)
    {
        $hots = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_name as product_name'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.is_delete' => 0, 'pf.online_status' => 1, 'p.is_delete' => 0, 'p.is_vip' => 0])
            ->limit($limit)
            ->get()->toArray();
        return $hots ? $hots : [];
    }

    /**
     * 与会员有关的热词
     * @param array $params
     * @return array
     */
    public static function fetchHotsAboutVip($params = [])
    {
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_name as product_name'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.is_delete' => 0, 'pf.online_status' => 1, 'p.is_delete' => 0, 'p.is_vip' => 0])
            ->limit($params['limit']);

        //普通用户或会员可以看产品
        $productVipIds = isset($params['productVipIds']) ? $params['productVipIds'] : [];
        $query->whereIn('p.platform_product_id', $productVipIds);

        $hots = $query->get();

        return $hots ? $hots->toArray() : [];
    }

    /**
     * 产品搜索
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * @param $data
     * @return array
     */
    public static function fetchSearchs($data)
    {
        //产品名称
        $productName = trim($data['productName']);
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        $productType = intval($data['productType']);

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0, 'p.is_vip' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //产品名称
        $query->when($productName, function ($query) use ($productName) {
            $query->where('platform_product_name', 'like', '%' . $productName . '%');
        });

        if ($productType == 1) {    //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience']);
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];

    }

    /**
     * 第二版 产品搜索
     * @param $data
     * @return array
     */
    public static function fetchSearchsAboutVip($data)
    {
        //产品名称
        $productName = trim($data['productName']);
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        $productType = intval($data['productType']);

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //产品名称
        $query->when($productName, function ($query) use ($productName) {
            $query->where('platform_product_name', 'like', '%' . $productName . '%');
        });

        //普通用户或会员可以看产品
        $productVipIds = isset($data['productVipIds']) ? $data['productVipIds'] : [];
        $query->whereIn('p.platform_product_id', $productVipIds);

        if ($productType == 1) {    //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience']);
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        }

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];

    }

    /**
     * @param $user
     * @param $data
     * @return bool
     * 创建搜索流水记录
     */
    public static function createSearchLog($user, $data)
    {
        $log = new PlatformProductSearchLog();
        $log->user_id = $data['userId'];
        $log->mobile = $user['mobile'];
        $log->username = $user['username'];
        $log->search_name = $data['productName'];
        $log->status = 0;
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        return $log->save();
    }

    /**
     * @param $data
     * @return bool
     * 搜索反馈
     */
    public static function createFeedback($data)
    {
        $feedback = new PlatformProductSearchFeedback();
        $feedback->user_id = $data['userId'];
        $feedback->content = trim($data['content']);
        $feedback->user_agent = UserAgent::i()->getUserAgent();
        $feedback->status = 0;
        $feedback->is_accept = 0;
        $feedback->created_at = date('Y-m-d H:i:s', time());
        $feedback->created_ip = Utils::ipAddress();
        return $feedback->save();
    }
}