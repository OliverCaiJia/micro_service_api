<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\BannerConfig;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\ProductPropertyfield;
use App\Models\Orm\ProductPropertylog;
use App\Models\Orm\UserCertify;
use App\Models\Orm\UserIdentity;
use App\Models\Orm\UserProfile;
use App\Strategies\ExactStrategy;
use App\Strategies\UserProfileStrategy;
use Illuminate\Support\Facades\DB;

class ExactFactory extends AbsModelFactory
{
    /**
     * @return array
     * 精确匹配 —— 广告图片
     */
    public static function fetchExactBanner()
    {
        $exactBanner = BannerConfig::select(['position', 'src as exact_img', 'app_url', 'h5_url'])
            ->where(['status' => 1, 'position' => 1])
            ->first();

        return $exactBanner ? $exactBanner->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 获取精确匹配数据
     */
    public static function fetchMatchData($userId)
    {
        $exactArr = UserProfile::select(['balance', 'balance_time', 'to_use', 'user_id'])
            ->where(['user_id' => $userId])
            ->first();

        return $exactArr ? $exactArr->toArray() : [];
    }

    /**
     * @param $userId
     * @param $data
     * 修改精确匹配数据
     */
    public static function updateExactMatchDatas($userId = '', $data = [])
    {
        //有值需要修改
        $profile = UserProfile::firstOrCreate(
            ['user_id' => $userId]
        );

        $profile->balance = !empty($data['loanMoney']) ? $data['loanMoney'] : $profile->balance;
        $profile->balance_time = !empty($data['loanTimes']) ? $data['loanTimes'] : $profile->balance_time;
        $profile->to_use = !empty($data['useType']) ? $data['useType'] : $profile->to_use;
        $profile->update_at = date('Y-m-d H:i:s', time());
        $profile->update_id = $userId;

        return $profile->save();
    }

    /**
     * @param $userId
     * @return array
     * 获取精确匹配数据
     */
    public static function fetchExactMatchDatas($userId)
    {
        //用户匹配信息   $user
        $user = ExactFactory::fetchMatchUserinfo($userId);

        //产品匹配字段
        $productinfo = ExactFactory::fetchMatchProdectinfo();

        //精确匹配数据处理
        $exactMatchArr = ExactStrategy::getExactMatchData($user, $productinfo);

        return $exactMatchArr;
    }

    /**
     * @param $userId
     * @return array
     * 精确匹配用户信息
     */
    public static function fetchMatchUserinfo($userId)
    {
        $certify = ExactFactory::fetchUserCertify($userId);
        $identity = ExactFactory::fetchUserIdentity($userId);
        $profile = ExactFactory::fetchUserProfile($userId);

        //精确匹配所需用户信息数据处理
        $user = ExactStrategy::getExactUserinfo($certify, $identity, $profile);

        return $user;
    }

    /**
     * @param $userId
     * @return array
     * 用户认证标签表
     */
    public static function fetchUserCertify($userId)
    {
        //用户认证标签表  $certify
        $certify = UserCertify::where(['user_id' => $userId])
            ->first();
        //print_r($certify);die;

        return $certify ? $certify->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 用户身份信息
     */
    public static function fetchUserIdentity($userId)
    {
        //用户身份信息表  $identity
        $identity = UserIdentity::where(['user_id' => $userId])
            ->first();
        //print_r($identity);

        return $identity ? $identity->toArray() : [];
    }

    /**
     * @param $userId
     * 用户基础信息
     */
    public static function fetchUserProfile($userId)
    {
        //用户基础信息表  $profile
        $profile = UserProfile::where(['user_id' => $userId])
            ->first();

        $profileArr = [];
        if ($profile) {
            $profileArr = $profile->toArray();
            $profileArr['age'] = UserProfileStrategy::ageToInt($profileArr['age']);
        }

        return $profile ? $profileArr : [];
    }

    /**
     * @return array
     * 精确匹配产品信息
     */
    public static function fetchMatchProdectinfo()
    {
        //分组查询  每一个产品对应的需要匹配的字段 sd_product_property_log
        $propertyArr = ExactFactory::fetchProductPropertylog();

        //查询表sd_product_property_field  寻找产品需要匹配的字段
        //需要匹配的值 $fieldArr
        $fieldArr = [];
        foreach ($propertyArr as $k => $v) {

            //产品信息
            $productArr = ProductFactory::productOne($v['product_id']);
            $fieldArr[$k]['productname'] = $productArr['platform_product_name'];
            $fieldArr[$k]['productId'] = $productArr['platform_product_id'];

            $propertyId = explode(',', $v['property_id']);
            $propertyData = ExactFactory::fetchProductPropertyfield($propertyId);
            foreach ($propertyData as $pk => $pv) {
                $propertyData[$pk]['necessity'] = '';
            }
            $fieldArr[$k]['productvalue'] = $propertyData;

        }
        //开始筛选产品需要的必要性字段 数据处理
        $productData = ExactStrategy::getMatchProdectinfo($fieldArr);

        return $productData;

    }

    /**
     * @return array
     * 每一个产品对应的需要匹配的字段
     */
    public static function fetchProductPropertylog()
    {
        $propertyArr = ProductPropertylog::select(['product_id',
            DB::raw('group_concat(property_id) as property_id'),
        ])
            ->groupBy('product_id')
            ->get()->toArray();

        return $propertyArr ? $propertyArr : [];
    }

    /**
     * @param $propertyId
     * 获取产品审核材料数据
     */
    public static function fetchProductPropertyfield($propertyId)
    {
        $propertyData = ProductPropertyfield::select([
            'field.product_property_filed_id', 'field.parent_id', 'field.score', 'field.value', 'property.is_necessity',
            'field.name', 'property.short_name',
        ])
            ->from('sd_product_property_field as field')
            ->join('sd_product_property as property', 'property.parent_id', '=', 'field.parent_id')
            ->whereIn('field.product_property_filed_id', $propertyId)
            ->where('field.value', '<>', 0)//去掉value=0的值， 剩下的就是主要匹配的值
            ->get()->toArray();

        return $propertyData ? $propertyData : [];
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * 得到精确匹配的产品信息
     * @param $data
     * @return array|bool
     */
    public static function fetchExactMatchProducts($data)
    {
        if (empty($data['productIdArr'])) {
            return false;
        }

        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        $loanMoney = isset($data['loanMoney']) ? $data['loanMoney'] : 500;
        $loanTimes = isset($data['loanTimes']) ? $data['loanTimes'] : 7;
        $useType = isset($data['useType']) ? $data['useType'] : 2;
        $key = $data['key'];

        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 100;
        //定位城市id
        $deviceId = $data['cityId'];
        //精确匹配产品id
        $productIdArr = $data['productIdArr'];
        //所有产品id
        $productIds = $data['productIds'];
        //产品城市关联表中的所有产品id
        $cityProductIds = $data['cityProductIds'];
        //地域对应产品id
        $deviceProductIds = $data['deviceProductIds'];

        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.fast_time', 'pro.value', 'p.satisfaction'])
            ->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience',
                'p.success_rate', 'p.loan_speed', 'p.composite_rate', 'p.loan_max'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.is_vip' => 0])
            ->whereIn('p.platform_product_id', $productIdArr);

        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);

        //地域
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            return $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });

        //借款金额
        if (!empty($loanMoney)) {
            $query->where([['loan_min', '<=', $loanMoney], ['loan_max', '>=', $loanMoney]]);
        }

        //借款期限
        if (!empty($loanTimes)) {
            $query->where([['period_min', '<=', $loanTimes], ['period_max', '>=', $loanTimes]]);
        }

        /* 条件 1还信用卡  2借现金 */
        if ($useType == 1) { //全部
            $query->where('p.to_use', '<>', 0);
        } elseif ($useType == 2) {   //借现金
            $query->where(['p.to_use' => 2]);
        } else {
            return false;
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
     * 第二版 精确匹配产品数据查询
     * @param array $data
     * @return array|bool
     */
    public static function fetchSecondEditionExactMatchProducts($data = [])
    {
        if (empty($data['productIdArr'])) {
            return false;
        }

        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        $loanMoney = isset($data['loanMoney']) ? $data['loanMoney'] : 500;
        $loanTimes = isset($data['loanTimes']) ? $data['loanTimes'] : 7;
        $useType = isset($data['useType']) ? $data['useType'] : 2;
        $key = $data['key'];

        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 100;
        //定位城市id
        $deviceId = $data['cityId'];
        //精确匹配产品id
        $productIdArr = $data['productIdArr'];
        //所有产品id
        $productIds = $data['productIds'];
        //产品城市关联表中的所有产品id
        $cityProductIds = $data['cityProductIds'];
        //地域对应产品id
        $deviceProductIds = $data['deviceProductIds'];

        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $productIdArr);

        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);

        //地域
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            return $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });

        //借款金额
        if (!empty($loanMoney)) {
            $query->where([['loan_min', '<=', $loanMoney], ['loan_max', '>=', $loanMoney]]);
        }

        //借款期限
        if (!empty($loanTimes)) {
            $query->where([['period_min', '<=', $loanTimes], ['period_max', '>=', $loanTimes]]);
        }

        //普通用户可以看产品
        $productVipIds = isset($data['productVipIds']) ? $data['productVipIds'] : [];
        $query->whereIn('p.platform_product_id', $productVipIds);

        /* 条件 1还信用卡  2借现金 */
        if ($useType == 1) { //全部
            $query->where('p.to_use', '<>', 0);
        } elseif ($useType == 2) {   //借现金
            $query->where(['p.to_use' => 2]);
        } else {
            return false;
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
}