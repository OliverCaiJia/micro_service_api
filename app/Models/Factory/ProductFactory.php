<?php

namespace App\Models\Factory;

use App\Constants\ProductConstant;
use App\Helpers\DateUtils;
use App\Helpers\LinkUtils;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\ApplyProcess;
use App\Models\Orm\CreditCardBanner;
use App\Models\Orm\DataProductApplyHistory;
use App\Models\Orm\DataProductApplyLog;
use App\Models\Orm\DataProductDetailLog;
use App\Models\Orm\FavouritePlatform;
use App\Models\Orm\Platform;
use App\Models\Orm\PlatformComment;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\PlatformProductFee;
use App\Models\Orm\PlatformProductRecommend;
use App\Models\Orm\PlatformProductRecommendType;
use App\Models\Orm\PlatformProductTagConfig;
use App\Models\Orm\PlatformProductTagType;
use App\Models\Orm\PlatformProductUserGroup;
use App\Models\Orm\PlatformProductVip;
use App\Models\Orm\ProductPropertylog;
use App\Models\Orm\ProductTag;
use App\Models\Orm\TagSeo;
use App\Models\Orm\UserProductBlack;
use App\Models\Orm\UserProductBlackTag;
use App\Models\Orm\UserProductBlackTagLog;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\PageStrategy;
use App\Strategies\ProductStrategy;
use Illuminate\Support\Facades\DB;

class ProductFactory extends AbsModelFactory
{

    /**
     * @param $productId
     * 返回单个产品logo && name
     */
    public static function getProductLogoAndName($productId)
    {
        $productObj = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.product_logo', 'p.platform_product_name', 'pf.platform_id', 'p.loan_min', 'p.loan_max'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.is_delete' => 0, 'pf.online_status' => 1, 'p.is_delete' => 0])
            ->where(['p.platform_product_id' => $productId])->first();
        return $productObj ? $productObj->toArray() : [];
    }

    /**
     * @param $productId
     * @return array
     * 获取产品额度
     */
    public static function fetchLoanMoneyById($productId)
    {
        $productObj = PlatformProduct::from('sd_platform_product as p')
            ->select(['loan_min', 'loan_max'])
            ->where(['p.is_delete' => 0])
            ->where(['p.platform_product_id' => $productId])->first();
        return $productObj ? $productObj->toArray() : [];
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * 获取首页诱导轮播数据
     * @return mixed
     */
    public static function fetchPromotions()
    {
        $product = PlatformProduct::where(['p.is_delete' => 0])
            ->from('sd_platform_product as p')
            ->where(['pf.is_delete' => 0, 'pf.online_status' => 1])
            ->where('p.is_roll', '>', 0)
            ->where(['p.is_vip' => 0])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->select('p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'p.loan_min', 'p.loan_max', 'p.product_logo')
            ->get()->toArray();

        return $product;
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * 新上线产品
     */
    public static function fetchNewOnlines()
    {
        $productLists = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.is_vip' => 0])
            ->select('p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'p.product_logo', 'p.product_introduct', 'p.update_date', 'p.create_date')
            ->orderBy('p.create_date', 'desc')
            ->limit(8)->get()->toArray();

        return $productLists;
    }

    /**
     * @param $data
     * @return bool
     * 返回分类产品对应id
     */
    public static function fetchSpecialId($data)
    {
        $bannerObj = CreditCardBanner::select(['product_list', 'title', 'img'])
            ->where(['id' => $data['specialId'], 'ad_status' => 0])
            ->first();
        return $bannerObj ? $bannerObj->toArray() : false;
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * 分类专题对应产品
     * @param $productIdArr
     * @param $key
     * @return array
     */
    public static function fetchSpecialProducts($productIdArr, $key)
    {
        //查询产品
        $productLists = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.is_vip' => 0])
            ->whereIn('p.platform_product_id', $productIdArr)
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.loan_max', 'p.loan_min', 'p.success_count', 'p.avg_quota', 'p.fast_time', 'pro.value'])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->get()->toArray();
        $collection = collect($productLists);
        $keyed = $collection->keyBy('platform_product_id')->toArray();

        return $keyed ? $keyed : [];
    }

    /**
     * 第二版 分类专题对应产品
     * @param array $params
     * @return array
     */
    public static function fetchProductSpecials($params = [])
    {
        //查询产品
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $params['productIds'])
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $params['key']]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $params['productVipIds']);

        $productLists = $query->get()->toArray();
        $collection = collect($productLists);
        $keyed = $collection->keyBy('platform_product_id')->toArray();
        return $keyed ? $keyed : [];
    }

    /**
     * @param array $data
     * 全部产品、借现金有关产品 1还信用卡  2借现金
     */
    public static function fetchProducts($data = [])
    {
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 1;
        $productType = $data['productType'];
        $useType = $data['useType'];

        /* 分页START */
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo']);

        /* 条件 1还信用卡  2借现金 */
        if ($useType == 1) {
            //全部
            $query->where('p.to_use', '<>', 0);
        } elseif ($useType == 2) {   //借现金
            $query->where(['p.to_use' => 2]);
        } else {
            return false;
        }

        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage)
            $pageSize = $countPage;
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页END */

        /* 排序 */
        $where = "";
        if ($productType == 1) {    //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience']);
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.create_date', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc');
        } elseif ($productType == 3) {  //放款速度
            $query->addSelect(['p.loan_speed']);
            $query->orderBy('p.loan_speed', 'desc');
        } elseif ($productType == 4) {  //贷款利率
            $query->addSelect(['p.composite_rate']);
            $query->orderBy('p.composite_rate', 'desc');
        } elseif ($productType == 5) {  //最高额度
            $query->addSelect(['p.loan_max']);
            $query->orderBy('p.loan_max', 'desc');
        } else {
            return false;
        }

        $product = $query->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $productArr['list'] = $product;
        $productArr['pageCount'] = $countPage;
        return $productArr;
    }

    /**
     * @return array
     * 产品搜索——获取tag_id
     */
    public static function fetchTagId($loanNeedArr)
    {
        $tagConfigArr = PlatformProductTagConfig::select(['tag_id'])
            ->whereIn('id', $loanNeedArr)
            ->pluck('tag_id')->toArray();
        return $tagConfigArr ? $tagConfigArr : [];
    }

    /**
     * 信用卡有关产品 1还信用卡
     */
    public static function fetchCreditCards()
    {
        $creditcardArr = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name', 'pf.platform_name',
                'p.product_introduct', 'p.product_logo', 'p.loan_speed', 'p.composite_rate', 'p.experience',
                'p.success_count'])
            ->where(['p.to_use' => 1])
            ->get()->toArray();

        return $creditcardArr;
    }

    /**
     * @param array $data
     * 产品详情——计算器
     */
    public static function fetchCalculators($data = [])
    {
        $productId = $data['productId'];
        //获取产品基础信息
        $productObj = PlatformProduct::where(['is_delete' => 0, 'platform_product_id' => $productId])
            ->select(['platform_id', 'interest_alg', 'min_rate', 'interest_alg', 'avg_quota', 'pay_method', 'loan_min',
                'loan_max', 'period_min', 'period_max'])
            ->first();
        if (empty($productObj)) {
            return false;
        }
        $productArr = $productObj->toArray();
        //平台
        $platformArr = Platform::where(['platform_id' => $productArr['platform_id'], 'online_status' => 1, 'is_delete' => 0])
            ->select(['platform_name'])->first();
        if (empty($platformArr)) {
            return false;
        }

        return $productArr ? $productArr : [];
    }

    /**
     * @param $param
     * 获取产品搜索标签
     */
    public static function fetchProductTagConfig($param)
    {
        $tagConfigArr = PlatformProductTagConfig::select(['id', 'value'])
            ->where(['key' => $param, 'status' => 1])
            ->get()->toArray();
        return $tagConfigArr ? $tagConfigArr : [];
    }

    /**
     * @param array $data
     * @return bool
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * 产品列表  Or  产品搜索
     */
    public static function fetchProductOrSearch($data = [], $deviceProductIds = [], $cityProductIds = [], $productIds = [], $deviceId = 0)
    {
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        $loanMoney = isset($data['loanMoney']) ? $data['loanMoney'] : 0;
        $indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.is_vip' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);

        //地域
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });

        //借款金额
        if (!empty($loanMoney)) {
            $query->where([['loan_min', '<=', $loanMoney], ['loan_max', '>=', $loanMoney]]);
        }

        //身份
        if (!empty($indent)) {
            $indent = ',' . $data['indent'];
            //获取身份对应的产品id
            //$productIdArr = ProductFactory::fetchProductIdFromIndent($indent);
            $query->where('user_group', 'like', '%' . $indent . '%');
        }

        //贷款类型
        //我需要
        if (!empty($loanNeed)) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        }

        //我有
        if (!empty($loanHas)) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            $loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
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


        /* 排序 */
        if ($productType == 1) {    //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience']);
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) {  //放款速度
            $query->addSelect(['p.loan_speed']);
            $query->orderBy('p.loan_speed', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) {  //贷款利率
            $query->addSelect(['p.composite_rate']);
            $query->orderBy('p.composite_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //最高额度
            $query->addSelect(['p.loan_max']);
            $query->orderBy('p.loan_max', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //新上线产品
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 7) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 8) { //新放款速度
            $query->addSelect(['pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } else {
            return false;
        }

        $productArr = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $countPage ? $countPage : 0;

        return $product ? $product : [];
    }

    /**
     * @param $productId
     * 获取产品名称
     */
    public static function fetchProductname($productId)
    {
        $productArr = PlatformProduct::select(['platform_product_id', 'platform_product_name', 'platform_id'])
            ->where(['is_delete' => 0])
            ->where(['platform_product_id' => $productId])
            ->first();
        if (empty($productArr)) {
            return false;
        }
        $platformArr = Platform::where(['platform_id' => $productArr['platform_id']])->first();
        if (!$platformArr) {
            return false;
        }
        return $productArr ? $productArr : [];
    }

    /** 获取产品
     * @param $productId
     * @return array|bool|\Illuminate\Database\Eloquent\Model|null|static
     */
    public static function fetchProduct($productId)
    {
        $productArr = PlatformProduct::select(['platform_product_id', 'platform_product_name', 'platform_id'])
            ->where(['platform_product_id' => $productId])
            ->first();
        if (empty($productArr)) {
            return false;
        }
        $platformArr = Platform::where(['platform_id' => $productArr['platform_id']])->first();
        if (!$platformArr) {
            return false;
        }
        return $productArr ? $productArr : [];
    }

    ///////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param $indent
     * 通过身份获取对应的product_id
     */
    public static function fetchProductIdFromIndent($indent)
    {
        $productIdArr = PlatformProductUserGroup::select(['platform_product_id'])
            ->where(['user_group_id' => $indent])
            ->pluck('platform_product_id')->toArray();
        return $productIdArr ? $productIdArr : [];
    }

    /**
     * @param array $data
     * 获取tag_id 对应 product_id
     */
    public static function fetchProductIdFromTagId($data = [])
    {
        //速贷大全标签
        $tagTypeId = ProductFactory::fetchProductTagTypeIdByNid(ProductConstant::PRODUCT_TAG_TYPE_LOAN);
        $productIdArr = ProductTag::select(['platform_product_id as product_id'])
            ->whereIn('tag_id', $data)
            ->where(['status' => 1])
            ->where(['type_id' => $tagTypeId])
            ->pluck('product_id')->toArray();
        //去重
        $productIdArr = array_flip($productIdArr);
        $productIdArr = array_flip($productIdArr);
        $productIdArr = array_values($productIdArr);
        return $productIdArr ? $productIdArr : [];
    }

    /**
     * @param $product
     * @return mixed
     * 分组求所有产品标签
     */
    public static function tagsAll($product = [])
    {
        if (empty($product)) {
            return false;
        }
        $tagIdArr = ProductTag::select([
            'tag_id',
            'platform_product_id',
            DB::raw('GROUP_CONCAT(DISTINCT tag_id) as   tag_id'),
        ])
            ->where('status', '!=', 9)
            ->groupBy('platform_product_id')
            ->get()->toArray();
        if ($tagIdArr) {
            foreach ($tagIdArr as $key => $val) {
                $tag_id = explode(',', $val['tag_id']);
                $tagAllArr = TagSeo::select(['name', 'font_color', 'boder_color', 'bg_color'])->whereIn('id', $tag_id)->get()->toArray();
                //以产品id作为键值
                $tagArr[$val['platform_product_id']]['tag_name'] = $tagAllArr;
            }
            foreach ($product as $pk => $pv) {
                $product[$pk]['tag_name'] = isset($tagArr[$pv['platform_product_id']]['tag_name']) ? $tagArr[$pv['platform_product_id']]['tag_name'] : [];
            }
        }
        return $product;
    }

    /**
     * @param array $product
     * @param $productId
     * @return array|bool
     * 单个产品的标签
     */
    public static function tagsOnly($product = [], $productId)
    {
        if (empty($product)) {
            return false;
        }
        //标签
        $tagIdArr = ProductTag::select(['tag_id', 'platform_product_id'])
            ->where([['status', '<>', 9], 'platform_product_id' => $productId])
            ->pluck('tag_id')->toArray();
        $tagArr = TagSeo::select(['name', 'font_color', 'boder_color', 'bg_color'])->whereIn('id', $tagIdArr)->get()->toArray();
        if (empty($tagArr)) {
            $product['tag_name'] = [];
        }
        foreach ($tagArr as $key => $val) {
            $product['tag_name'][$key]['name'] = isset($val['name']) ? $val['name'] : [];
            $product['tag_name'][$key]['font_color'] = isset($val['font_color']) ? $val['font_color'] : [];
            $product['tag_name'][$key]['boder_color'] = isset($val['boder_color']) ? $val['boder_color'] : [];
            $product['tag_name'][$key]['bg_color'] = isset($val['bg_color']) ? $val['bg_color'] : [];
        }
        return $product;
    }

    /**
     * @param $productId
     * @return array
     * 产品对应标签id
     */
    public static function fetchProductTagsIdsOnly($productId, $typeId = 0)
    {
        $tagIds = ProductTag::select(['tag_id', 'platform_product_id'])
            ->where([['status', '<>', 9], 'platform_product_id' => $productId])
            ->where(['type_id' => $typeId])
            ->orderBy('position')
            ->pluck('tag_id')->toArray();
        return $tagIds ? $tagIds : [];
    }

    /**
     * @param $tagIds
     * @return array
     * 产品对应标签的名称
     */
    public static function fetchSeoTagsIdsOnly($tagIds)
    {
        $condition = implode(',', $tagIds);
        $query = TagSeo::select(['id', 'name', 'font_color', 'boder_color', 'bg_color'])
            ->whereIn('id', $tagIds);
        //排序条件
        $query->when($condition, function ($query) use ($condition) {
            $query->orderByRaw(DB::raw("FIELD(`id`, " . $condition . ')'));
        });
        $tags = $query->get()->toArray();
        return $tags ? $tags : [];
    }

    /**
     * @param array $product
     * @param $productId
     * @return array|bool
     * 按id排序 产品标签
     */
    public static function tagsByOne($product = [], $productId)
    {
        if (empty($product)) {
            return false;
        }
        //速贷大全对应id
        $typeId = self::fetchProductTagTypeIdByNid(ProductConstant::PRODUCT_TAG_TYPE_LOAN);
        $tagIds = self::fetchProductTagsIdsOnly($productId, $typeId);
        $tags = self::fetchSeoTagsIdsOnly($tagIds);
        foreach ($tags as $key => $val) {
            $product['tag_name'][$key]['name'] = isset($val['name']) ? $val['name'] : [];
            $product['tag_name'][$key]['font_color'] = isset($val['font_color']) ? $val['font_color'] : [];
            $product['tag_name'][$key]['boder_color'] = isset($val['boder_color']) ? $val['boder_color'] : [];
            $product['tag_name'][$key]['bg_color'] = isset($val['bg_color']) ? $val['bg_color'] : [];
        }
        return $product;
    }

    /**
     * @return array
     * 分组获取产品标签与position
     */
    public static function fetchProductTagsIdsAll($typeNid = '')
    {
        $tagIdsAndPositions = ProductTag::select([
            'tag_id',
            'platform_product_id',
            DB::raw('GROUP_CONCAT(tag_id) as tag_id'),
            DB::raw('GROUP_CONCAT(position) as position'),
        ])
            ->where('status', '!=', 9)
            ->where(['type_id' => $typeNid])
            ->groupBy('platform_product_id')
            ->get()->toArray();
        //dd($tagIdsAndPositions);
        return $tagIdsAndPositions ? $tagIdsAndPositions : [];
    }

    /**
     * 第二版 产品标签 获取position最小的标签id
     * @return array
     */
    public static function fetchProductTag($typeId = 0)
    {
        $tagIdsAndPositions = ProductTag::select([
            'platform_product_id',
            DB::raw('GROUP_CONCAT(tag_id) as tag_id'),
            DB::raw('GROUP_CONCAT(position) as position'),
        ])
            ->where('status', '!=', 9)
            ->where(['type_id' => $typeId])
            ->groupBy('platform_product_id')
            ->get()->toArray();
        //dd($tagIdsAndPositions);
        return $tagIdsAndPositions ? $tagIdsAndPositions : [];
    }

    /**
     * @param array $product
     * @return array|bool
     * 按id排序  获取产品列表的标签
     */
    public static function tagsByAll($product = [])
    {
        if (empty($product)) {
            return false;
        }
        //速贷大全对应id
        $typeId = self::fetchProductTagTypeIdByNid(ProductConstant::PRODUCT_TAG_TYPE_LOAN);
        $tagIdsAndPositions = self::fetchProductTagsIdsAll($typeId);
        $tagIds = ProductStrategy::fetchTagsIdsByPostion($tagIdsAndPositions);
        //dd($tagIds);
        if ($tagIds) {
            foreach ($tagIds as $key => $val) {
                $tag_id = explode(',', $val['tag_id']);
                $condition = $val['tag_id'];
                $query = TagSeo::select(['name', 'font_color', 'boder_color', 'bg_color'])
                    ->whereIn('id', $tag_id);
                //排序条件
                $query->when($val['tag_id'], function ($query) use ($condition) {
                    $query->orderByRaw(DB::raw("FIELD(`id`, " . $condition . ')'));
                });

                $tagAllArr = $query->get()->toArray();
                //以产品id作为键值
                $tagArr[$val['platform_product_id']]['tag_name'] = $tagAllArr;
            }
        }
        foreach ($product as $pk => $pv) {
            $product[$pk]['tag_name'] = isset($tagArr[$pv['platform_product_id']]['tag_name']) ? $tagArr[$pv['platform_product_id']]['tag_name'] : [];
        }
        return $product;
    }

    /**
     * 产品列表标签  获取第一个标签作为产品标签
     * @param array $product
     * @return array|bool
     */
    public static function tagsLimitOneToProducts($product = [])
    {
        //速贷大全标签
        $typeId = self::fetchProductTagTypeIdByNid(ProductConstant::PRODUCT_TAG_TYPE_LOAN);
        $tagIds = self::fetchProductTag($typeId);
        $tagIds = ProductStrategy::fetchTagsIdsByPostion($tagIds);
        //根据标签id查找标识名称
        if ($tagIds) {
            foreach ($tagIds as $key => $val) {
                $tag_id = explode(',', $val['tag_id']);
                $query = TagSeo::select(['name'])
                    ->where(['id' => $tag_id[0]]);
                $tag = $query->first()->toArray();
                //以产品id作为键值
                $tagArr[$val['platform_product_id']]['tag_name'] = $tag ? $tag['name'] : '';
            }
        }

        foreach ($product as $pk => $pv) {
            $product[$pk]['tag_name'] = isset($tagArr[$pv['platform_product_id']]['tag_name']) ? $tagArr[$pv['platform_product_id']]['tag_name'] : '';
        }

        return $product;
    }


    /**
     * 单个产品详情
     * @param $productId
     * @return bool
     */
    public static function productOne($productId)
    {
        $productObj = PlatformProduct::where(['is_delete' => 0, 'platform_product_id' => $productId])
            ->first();
        if (empty($productObj)) {
            return false;
        }
        $productArr = $productObj->toArray();
        $platformObj = Platform::where(['platform_id' => $productArr['platform_id'], 'online_status' => 1, 'is_delete' => 0])
            ->select(['platform_name'])->first();
        if (empty($platformObj)) {
            return false;
        }
        $productArr['platform_name'] = $platformObj->platform_name;
        return $productArr;
    }

    /**
     * @param $id
     * @return array
     * @desc    申请流程
     * api4
     */
    public static function applicationProcess($id)
    {
        $ids = explode(',', $id);
        $process = [];
        foreach ($ids as $key => $v) {
            $val = ApplyProcess::where(['id' => $v])->first();
            if ($val) {
                $val = $val->toArray();
                $process[$key]['id'] = isset($val['id']) ? $val['id'] : 0;
                $process[$key]['name'] = isset($val['name']) ? $val['name'] : '';
                $process[$key]['img'] = isset($val['img']) ? QiniuService::getImgs($val['img']) : '';
            }
        }
        return $process;
    }

    /**
     * 统计通过率
     * api4
     */
    public static function passRate($productId)
    {
        $pid = $productId;
        // 0=>1 1=>2 2=>3 3=>4 4=>5
        //通过率 = 评论结果值  1+5 /(1+2+5)  1，拿到钱 2，悲剧咧 3 其他 4 申请中 ， 5 有额度，
        $one = PlatformComment::where(['platform_product_id' => $pid, 'result' => 1])->count();
        $two = PlatformComment::where(['platform_product_id' => $pid, 'result' => 2])->count();
        $five = PlatformComment::where(['platform_product_id' => $pid, 'result' => 5])->count();

        if (($one + $two + $five) > 0) {
            $oneTwo = bcadd($one, $five);
            $oneFive = bcadd($one, bcadd($two, $five));
            $pass_rate = bcdiv($oneTwo, $oneFive, 2);
        } else {
            $pass_rate = 0;
        }

        return $pass_rate;
    }

    /**
     * @param $creditProId
     * 返回下限的产品id
     */
    public static function updateProductApply($creditProId)
    {
        $productIdArr = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $creditProId)
            ->select('p.platform_product_id')
            ->pluck('p.platform_product_id')
            ->toArray();
        $productId = array_diff($creditProId, $productIdArr);
        return $productId;
    }

    /**
     * @param $userId
     * @param $productId
     * @return array
     * 通过user_id && product_id 获取收藏信息
     */
    public static function fetchCollectionByUidAndPid($userId, $productId)
    {
        $collectionObj = FavouritePlatform::select('platform_product_id')
            ->where(['user_id' => $userId])
            ->where(['platform_product_id' => $productId])
            ->first();

        return $collectionObj ? $collectionObj->toArray() : [];
    }

    /**
     * @param $productId
     * 增加产品点击量
     */
    public static function updateProductClick($productId)
    {
        $product = PlatformProduct::select()->where(['platform_product_id' => $productId])->first();
        $product->increment('click_count', 1);
        return $product->save();
    }

    /**
     * @return array
     * 所有产品id
     */
    public static function fetchProductIds()
    {
        $productIds = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.product_logo', 'p.platform_product_name', 'pf.platform_id'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.is_delete' => 0, 'pf.online_status' => 1, 'p.is_delete' => 0])
            ->pluck('p.platform_product_id')->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 首页推荐产品  显示32条产品  默认排序
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * @param array $data
     * @return mixed
     */
    public static function fetchRecommends($data = [])
    {
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.success_rate', 'p.product_logo', 'p.loan_min', 'p.loan_max', 'p.terminal_type'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.is_vip' => 0])
            ->orderBy('p.position_sort', 'asc')
            ->orderBy('p.create_date', 'desc')
            ->orderBy('p.update_date', 'desc');

        //根据终端类型筛选产品
        $terminalType = $data['terminalType'];
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        $count = $query->count();
        $page = PageStrategy::getPage($count, $data['pageSize'], $data['pageNum']);
        $productArr = $query
            ->limit($page['limit'])
            ->offset($page['offset'])
            ->get()->toArray();

        $product['list'] = $productArr;
        $product['pageCount'] = $page['pageCount'] ? $page['pageCount'] : 0;

        return $product;
    }

    /**
     * @param $platformId
     * @param string $productId
     * @return array
     * 计算器
     */
    public static function fetchCounter($productId)
    {
        //获取产品基础信息
        $products = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.platform_product_id' => $productId])
            ->select(['p.platform_id', 'p.interest_alg', 'p.min_rate', 'p.interest_alg', 'p.avg_quota', 'p.pay_method', 'p.loan_min', 'p.loan_max', 'p.period_min', 'p.period_max'])
            ->first();

        return $products ? $products->toArray() : [];
    }

    /**
     * @param $userId
     * @param array $data
     * @param array $user
     * @param array $products
     * @param array $deliverys
     * @return bool
     * 查看产品详情流水
     */
    public static function createProductLog($userId, $data = [], $user = [], $products = [], $deliverys = [])
    {
        $productLog = new DataProductDetailLog();
        $productLog->user_id = $userId;
        $productLog->username = $user['username'];
        $productLog->mobile = $user['mobile'];
        $productLog->platform_id = $products['platform_id'];
        $productLog->platform_product_id = $products['platform_product_id'];
        $productLog->platform_product_name = $products['platform_product_name'];
        $productLog->user_agent = UserAgent::i()->getUserAgent();
        $productLog->channel_id = isset($deliverys['id']) ? $deliverys['id'] : '';
        $productLog->channel_title = isset($deliverys['title']) ? $deliverys['title'] : '';
        $productLog->channel_nid = isset($deliverys['nid']) ? $deliverys['nid'] : '';
        $productLog->create_at = date('Y-m-d H:i:s', time());
        $productLog->create_ip = Utils::ipAddress();
        return $productLog->save();

    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * @param $data
     * @return array|bool
     * 速贷大全列表 or 速贷大全搜索列表
     */
    public static function fetchProductsOrSearchs($data)
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        $loanMoney = isset($data['loanMoney']) ? $data['loanMoney'] : 0;
        $indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;
        //所有产品id
        $productIds = $data['productIds'];
        //产品城市关联表中的所有产品id
        $cityProductIds = $data['cityProductIds'];
        //地域对应产品id
        $deviceProductIds = $data['deviceProductIds'];
        //地域id
        $deviceId = $data['deviceId'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0, 'p.is_vip' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);

        //地域
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });

        //根据终端类型筛选产品
        $terminalType = $data['terminalType'];
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //借款金额
        $query->when($loanMoney, function ($query) use ($loanMoney) {
            $query->where([['loan_min', '<=', $loanMoney], ['loan_max', '>=', $loanMoney]]);
        });

        //身份
        $query->when($indent, function ($query) use ($indent) {
            $indent = ',' . $indent;
            //获取身份对应的产品id
            $query->where('user_group', 'like', '%' . $indent . '%');
        });

        //贷款类型
        //我需要
        $query->when($loanNeed, function ($query) use ($loanNeed) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        //我有
        $query->when($loanHas, function ($query) use ($loanHas) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            //$loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        /* 排序 */
        if ($productType == 1) {     //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience']);
            $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
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

    /*** 马甲包速贷大全工厂方法
     * @param array $data
     * @return array|bool
     */
    public static function fetchProductsShadow($data = [])
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;

        //分页
        $pageSize = isset($data['pageSize']) ? intval($data['pageSize']) : 1;
        $pageNum = isset($data['pageNum']) ? intval($data['pageNum']) : 10;

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'po.position_sort'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->Leftjoin('sd_platform_product_position as po', 'p.platform_product_id', '=', 'po.product_id')
            ->where(['po.online_status' => 1]);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        /* 排序 */
        if ($productType == 1) {     //综合指数
            $query->addSelect(['po.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience']);
            $query->orderBy('po.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
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
     * @return array
     * 代还信用卡产品
     * 最多显示10个
     */
    public static function fetchGiveBackProducts()
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.fast_time', 'pro.value', 'p.interest_alg', 'p.min_rate', 'p.avg_quota'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->orderBy('p.position_sort', 'asc')
            ->orderBy('p.platform_product_id', 'desc')
            ->limit(10)->get()->toArray();

        return $query ? $query : [];

    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * @param $data
     * @return array
     * 还款提醒中的推荐产品
     */
    public static function fetchAccountAlertProducts($data)
    {
        //分页
        $pageSize = $data['pageSize'];
        $pageNum = $data['pageNum'];
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $data['productIds'])
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.fast_time', 'pro.value', 'p.interest_alg', 'p.min_rate', 'p.avg_quota'])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $data['key']])
            ->where(['p.is_vip' => 0])
            ->orderByRaw(DB::raw("FIELD(`p`.`platform_product_id`, " . $data['condition'] . ')'));
        //排序
        $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');

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
     * @param $typeNid
     * @return array
     * 代还产品id
     */
    public static function fetchSpecialProductIdsByTypeNid($typeNid)
    {
        $productIds = CreditCardBanner::select(['product_list'])
            ->where(['type_nid' => $typeNid, 'ad_status' => 0])
            ->orderBy('utime', 'desc')
            ->limit(1)
            ->first();
        return $productIds ? $productIds->toArray() : [];
    }

    /**
     * @is_vip 区分vip, 1为vip产品, 0为非vip产品
     * @param $data
     * @return array
     * 代还对应分类专题产品
     */
    public static function fetchSpecialProductsByTypeNid($data)
    {
        //查询产品
        $productLists = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->whereIn('p.platform_product_id', $data['productIds'])
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'p.fast_time', 'pro.value', 'p.interest_alg', 'p.min_rate', 'p.avg_quota'])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $data['key']])
            ->where(['p.is_vip' => 0])
            ->orderByRaw(DB::raw("FIELD(`p`.`platform_product_id`, " . $data['condition'] . ')'))
            ->limit(20)
            ->get()->toArray();

        return $productLists ? $productLists : [];
    }

    /**
     * 用户产品申请记录
     * @param array $data
     * @return array
     */
    public static function fetchApplyHistorysByUserId($data = [])
    {
        $log = DataProductApplyHistory::select(['id', 'platform_product_id', 'is_urge', 'created_at', 'user_id', 'platform_id'])
            ->where(['user_id' => $data['userId']])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();

        return $log ? $log : [];
    }

    /**
     * @param array $params
     * @return array
     * 产品申请记录产品
     */
    public static function fetchHistoryProducts($params = [])
    {
        foreach ($params as $key => $val) {
            $product = PlatformProduct::from('sd_platform_product as p')
                ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
                ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
                ->where('p.platform_product_id', $val['platform_product_id'])
                ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                    'p.product_logo', 'p.loan_min', 'p.loan_max', 'p.period_min', 'p.period_max', 'p.service_mobile', 'p.interest_alg'])
                ->first();
            $params[$key]['product'] = $product ? $product->toArray() : [];
        }

        return $params;
    }

    /**
     * @param $data
     * @return array
     * 判断是否可以进行催审
     */
    public static function fetchUrgeById($data)
    {
        $urge = DataProductApplyHistory::select(['id'])
            ->where(['is_urge' => 1, 'id' => $data['urgeId']])
            ->first();

        return $urge ? $urge->toArray() : [];
    }

    /**
     * @param $params
     * @return mixed
     * 修改催审状态为已催审
     */
    public static function updateHistoryUrge($params)
    {
        return DataProductApplyHistory::where(['id' => $params['urgeId'], 'is_urge' => 0])->update(['is_urge' => 1]);
    }

    /**
     * 产品列表 & 速贷大全筛选 数据
     * @param array $data
     * @return array|bool
     */
    public static function fetchProductsOrFilters($data = [])
    {
        //产品类型
        $productType = isset($data['productType']) ? intval($data['productType']) : 1;
        //身份
        //$indent = isset($data['indent']) ? intval($data['indent']) : 0;
        //贷款类型
        $loanNeed = isset($data['loanNeed']) ? $data['loanNeed'] : '';
        $loanHas = isset($data['loanHas']) ? $data['loanHas'] : '';
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);
        //贷款金额
        $loanAmount = empty($data['loanAmount']) ? [] : explode(',', $data['loanAmount']);
        //贷款期限
        $loanTerm = empty($data['loanTerm']) ? [] : explode(',', $data['loanTerm']);

        //所有产品id
        $productIds = $data['productIds'];
        //产品城市关联表中的所有产品id
        $cityProductIds = $data['cityProductIds'];
        //地域对应产品id
        $deviceProductIds = $data['deviceProductIds'];
        //地域id
        $deviceId = $data['deviceId'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'pro.value', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $data['productVipIds']);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //不想看产品筛选
        $blackIds = $data['blackIds'];
        $query->when($blackIds, function ($query) use ($blackIds) {
            $query->whereNotIn('p.platform_product_id', $blackIds);
        });

        //地域筛选
        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });


        //借款金额
        $query->when($loanAmount, function ($query) use ($loanAmount) {
            $query->where(function ($query) use ($loanAmount) {
                if (empty($loanAmount[1])) {
                    $query->where('loan_min', '>', $loanAmount[0])->orWhere('loan_max', '>', $loanAmount[0]);
                } else {
                    $query->whereBetween('loan_min', $loanAmount)->orWhereBetween('loan_max', $loanAmount);
                    $query->orWhere(function ($query) use ($loanAmount) {
                        $query->where('loan_min', '<', $loanAmount[0])->where('loan_max', '>', $loanAmount[1]);
                    });
                }
            });
        });

        //借款期限
        $query->when($loanTerm, function ($query) use ($loanTerm) {
            $query->where(function ($query) use ($loanTerm) {
                if (empty($loanTerm[1])) {
                    $query->where('period_min', '>', $loanTerm[0])->orWhere('period_max', '>', $loanTerm[0]);
                } else {
                    $query->whereBetween('period_min', $loanTerm)->orWhereBetween('period_max', $loanTerm);
                    $query->orWhere(function ($query) use ($loanTerm) {
                        $query->where('period_min', '<', $loanTerm[0])->where('period_max', '>', $loanTerm[1]);
                    });
                }
            });
        });

        //贷款类型
        //我需要
        $query->when($loanNeed, function ($query) use ($loanNeed) {
            $loanNeedArr = explode(',', $loanNeed);
            //获取对应的tag_id的标签
            //$loanNeedArr = ProductFactory::fetchTagId($loanNeedArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanNeedArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        //我有
        $query->when($loanHas, function ($query) use ($loanHas) {
            $loanHasArr = explode(',', $loanHas);
            //获取对应的tag_id的标签
            //$loanHasArr = ProductFactory::fetchTagId($loanHasArr);
            //获取对应产品id
            $productIdArr = ProductFactory::fetchProductIdFromTagId($loanHasArr);
            $query->whereIn('platform_product_id', $productIdArr);
        });

        /* 排序 */
        if ($productType == 1) {     //综合指数
            $query->addSelect(['p.position_sort', 'p.composite_rate', 'p.loan_speed', 'p.experience', 'p.is_vip']);
            $query->orderBy('p.is_vip', 'asc')
                ->orderBy('p.position_sort', 'asc')
                ->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 2) {  //成功率
            $query->addSelect(['p.success_rate']);
            $query->orderBy('p.success_rate', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 3) { //新上线产品
            $query->addSelect(['p.create_date', 'p.online_at']);
            $query->orderBy('p.online_at', 'desc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 4) { //新放款速度
            $query->addSelect(['p.composite_rate', 'p.loan_speed', 'p.experience', 'pro.value']);
            $query->orderBy(DB::raw('pro.value*1'))->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 5) {  //贷款利率
            $query->addSelect(['p.month_rate', 'p.interest_alg', 'p.min_rate']);
            $query->orderBy('p.month_rate', 'asc')->orderBy('p.platform_product_id', 'desc');
        } elseif ($productType == 6) { //平均额度
            $query->addSelect(['p.avg_quota', 'p.loan_max', 'p.loan_min']);
            $query->orderBy('p.avg_quota', 'desc')->orderBy('p.platform_product_id', 'desc');
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
     * 同类产品推荐
     * @param array $data
     * @return array
     */
    public static function fetchLikeProducts($data = [])
    {
        //分页 默认显示5条数据
        $pageSize = isset($data['pageSize']) ? $data['pageSize'] : 1;
        $pageNum = isset($data['pageNum']) ? $data['pageNum'] : 5;

        //所有产品id
        $productIds = $data['productIds'];
        //产品城市关联表中的所有产品id
        $cityProductIds = $data['cityProductIds'];
        //地域对应产品id
        $deviceProductIds = $data['deviceProductIds'];
        //地域id
        $deviceId = $data['deviceId'];

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link',
                'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type',
                'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.is_preference', 'p.type_nid'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //用户可以看产品
        $query->whereIn('p.platform_product_id', $data['productVipIds']);

        //排除该详情的产品id
        $productId = isset($data['productId']) ? $data['productId'] : '';
        $query->when($productId, function ($query) use ($productId) {
            $query->where('p.platform_product_id', '!=', $productId);
        });

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //不想看产品筛选
        $blackIds = $data['blackIds'];
        $query->when($blackIds, function ($query) use ($blackIds) {
            $query->whereNotIn('p.platform_product_id', $blackIds);
        });

        //地域筛选
        $diff = array_diff($productIds, $cityProductIds);
        $deviceProductIdDatas = array_merge($diff, $deviceProductIds);
        $query->when($deviceId, function ($query) use ($deviceProductIdDatas) {
            $query->whereIn('p.platform_product_id', $deviceProductIdDatas);
        });

        //定位排序
        $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');

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
     * 会员对应可以查看的产品ids
     * @status 状态, 1使用中, 0未使用
     * @param array $params
     * @return array
     */
    public static function fetchProductVipIdsByVipTypeId($params = [])
    {
        $productIds = PlatformProductVip::select(['product_id'])
            ->where(['vip_type_id' => $params['userVipType'], 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 用户不想看产品ids
     * @status 状态, 1有效, 0无效
     * @param array $params
     * @return array
     */
    public static function fetchBlackIdsByUserId($params = [])
    {
        $ids = UserProductBlack::select(['product_id'])
            ->where(['user_id' => $params['userId'], 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $ids ? $ids : [];
    }

    /**
     * 详情页标签类型表 类型id
     * @status 状态, 1使用中, 0未使用
     * @param string $param
     * @return string
     */
    public static function fetchApprovalConditionTypeId($param = '')
    {
        $typeId = PlatformProductTagType::select('id')
            ->where(['type_nid' => $param, 'status' => 1])
            ->first();

        return $typeId ? $typeId->id : '';
    }

    /**
     * 详情页标签类型表 标签
     * @param array $params
     * @return array
     */
    public static function fetchDetailTags($params = [])
    {
        $tagIds = self::fetchProductTagsIdsOnly($params['productId'], $params['type_id']);
        $tags = self::fetchSeoTagsIdsOnly($tagIds);
        $seoTag = [];
        foreach ($tags as $key => $val) {
            $seoTag[$key]['name'] = isset($val['name']) ? $val['name'] : [];
            $seoTag[$key]['font_color'] = isset($val['font_color']) ? $val['font_color'] : [];
            $seoTag[$key]['boder_color'] = isset($val['boder_color']) ? $val['boder_color'] : [];
            $seoTag[$key]['bg_color'] = isset($val['bg_color']) ? $val['bg_color'] : [];
        }
        return $seoTag;
    }

    /**
     * 第二版 首页今日良心推荐
     * @param array $data
     * @return mixed
     */
    public static function fetchSecondEditionRecommends($data = [])
    {
        $query = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->select('p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.loan_min', 'p.loan_max','p.type_nid','p.is_preference')
            ->orderBy('p.platform_product_id', 'desc')
            ->whereIn('p.platform_product_id', $data['recommendIds'])
            ->limit($data['limit']);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        $productLists = $query->get()->toArray();

        return $productLists;
    }

    /**
     * 查询产品黑名单存在状态
     * @param array $params
     * @return int
     */
    public static function fetchProductBlackStatus($params = [])
    {
        $black = UserProductBlack::select(['status'])
            ->where(['user_id' => $params['userId'], 'product_id' => $params['productId']])
            ->first();

        return $black ? $black->status : 0;
    }

    /**
     * 创建产品黑名单
     * @status 状态, 1有效, 0无效
     * @param array $params
     * @return bool
     */
    public static function updateProductBlack($params = [])
    {
        $query = UserProductBlack::where(['user_id' => $params['userId'], 'product_id' => $params['productId']])
            ->first();
        if (empty($query)) {
            $query = new UserProductBlack();
            $query->created_at = date('Y-m-d H:i:s', time());
            $query->created_ip = Utils::ipAddress();
        }

        $query->user_id = $params['userId'];
        $query->product_id = $params['productId'];
        $query->status = 1;
        $query->updated_at = date('Y-m-d H:i:s', time());
        $query->updated_ip = Utils::ipAddress();
        return $query->save();
    }

    /**
     * 取消产品不想看状态
     * @status 状态, 1有效, 0无效
     * @param array $params
     * @return string
     */
    public static function deleteProductBlack($params = [])
    {
        $query = UserProductBlack::where(['user_id' => $params['userId'], 'product_id' => $params['productId']])
            ->update([
                'status' => 0,
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);

        return $query ? $query : '';
    }

    /**
     * 根据产品id查询产品对应费率
     * @type  类型,1 默认 2 模板
     * @status 状态, 1有效, 0无效
     * @operator 计算方式, 1加法方式, 2利率方式
     * @date_relate  日期相关, 1相关, 0不相关
     * @param $productId
     * @return array
     */
    public static function fetchProductFee($productId)
    {
        $fee = PlatformProductFee::select(['id', 'product_id', 'operator', 'name', 'type_nid', 'value', 'date_relate', 'status', 'remark'])
            ->where(['product_id' => $productId, 'status' => 1, 'type' => 1])
            ->orderBy('position_sort', 'asc')
            ->orderBy('id', 'desc')
            ->get()->toArray();

        return $fee ? $fee : [];
    }

    /**
     * 产品推荐配置类型表
     * @status 状态, 1 有效, 0 无效
     * @param $typeNid
     * @return string
     */
    public static function fetchPlatformProductRecommendTypeIdByNid($typeNid)
    {
        $typeId = PlatformProductRecommendType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $typeId ? $typeId->id : '';
    }

    /**
     * 符合typeid的产品id
     * @status 状态, 1 有效, 0 无效
     * @param array $params
     * @return array
     */
    public static function fetchRecommendIdsByTypeId($params = [])
    {
        $productIds = PlatformProductRecommend::select(['product_id'])
            ->where(['type_id' => $params['typeId'], 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $productIds ? $productIds : [];
    }

    /**
     * 黑名单产品
     * @param array $params
     * @return array
     */
    public static function fetchProductBlackIdsInfo($params = [])
    {
        $blacks = UserProductBlack::select(['product_id', 'updated_at'])
            ->where(['user_id' => $params['userId']])
            ->whereIn('product_id', $params['mergeBlackIds'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();

        return $blacks ? $blacks : [];
    }

    /**
     * 不想看所有产品id
     * @param array $params
     * @return array
     */
    public static function fetchProductBlackIds($params = [])
    {
        $blackIds = UserProductBlack::select(['product_id'])
            ->where(['user_id' => $params['userId'], 'status' => 1])
            ->pluck('product_id')
            ->toArray();

        return $blackIds ? $blackIds : [];
    }

    /**
     * 不想看产品列表
     * @param array $params
     * @return array
     */
    public static function fetchProductBlacks($params = [])
    {
        $products = [];
        foreach ($params as $key => $val) {
            $product = ProductFactory::productOne($val['product_id']);
            if ($product) {
                $products[$key]['platform_product_id'] = $product['platform_product_id'];
                $products[$key]['platform_id'] = $product['platform_id'];
                $products[$key]['platform_product_name'] = $product['platform_product_name'];
                $products[$key]['product_logo'] = QiniuService::getProductImgs($product['product_logo'], $val['product_id']);
                $products[$key]['shielding_time'] = $val['updated_at'];
            }
        }

        return array_values($products);
    }

    /**
     * 不想看 产品总个数
     * @param array $params
     * @return int
     */
    public static function fetchBlackCountsById($params = [])
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;

        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.platform_product_id', $params['blackIds']);

        return $query ? $query->count() : 0;
    }

    /**
     * 在线产品总个数
     * @param array $data
     * @return int
     */
    public static function fetchProductCounts($data = [])
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;

        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key])
            ->whereIn('p.platform_product_id', $data['productIds']);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        $counts = $query->count();

        return $counts ? $counts : 0;
    }

    /**
     * 获取产品ids
     *
     * @param $typeId
     * @return mixed
     */
    public static function fetchProductVipIds($typeId)
    {
        $data = PlatformProductVip::where(['vip_type_id' => $typeId, 'status' => 1])
            ->pluck('product_id')->toArray();

        return $data ? $data : [];
    }

    /**
     * @return int
     * 产品今日申请总量
     */
    public static function fetchTodayApplyCount()
    {
        $today = date('Y-m-d');
        $todayApply = DataProductApplyLog::where('create_at', '>=', $today . ' 00:00:00')->where('create_at', '<', $today . ' 23:59:59')->count();

        return $todayApply ? $todayApply : 0;
    }

    /**
     * 获取所有在线产品今日申请量total_today_count总和
     * @return int
     */
    public static function fetchTodayApplyCountByTotalTodayCount()
    {
        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;

        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        $count = $query->sum('total_today_count');

        return $count ? $count : 0;
    }

    /**
     * 滑动专题产品
     * @param array $data
     * @return array
     */
    public static function fetchSlideProducts($data = [])
    {
        //分页
        $pageSize = intval($data['pageSize']);
        $pageNum = intval($data['pageNum']);

        //放款时间
        $key = ProductConstant::PRODUCT_LOAN_TIME;
        //查询
        $query = PlatformProduct::from('sd_platform_product as p')
            ->select(['p.platform_product_id', 'p.platform_id', 'p.platform_product_name',
                'p.product_introduct', 'p.product_logo', 'p.success_count', 'pf.h5_register_link', 'p.click_count', 'p.fast_time', 'pro.value', 'p.satisfaction', 'p.terminal_type', 'p.period_min', 'p.period_max', 'p.loan_min', 'p.loan_max', 'p.interest_alg', 'p.total_today_count', 'p.min_rate', 'p.max_rate'])
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->join('sd_platform_product_property as pro', 'p.platform_product_id', '=', 'pro.product_id')
            ->where(['pro.key' => $key]);

        //普通用户可以看产品
        $query->whereIn('p.platform_product_id', $data['productVipIds']);
        //配置推荐产品
        $query->whereIn('p.platform_product_id', $data['productIds']);

        //根据终端类型筛选产品
        $terminalType = isset($data['terminalType']) ? $data['terminalType'] : '';
        $query->when($terminalType, function ($query) use ($terminalType) {
            $query->where(function ($query) use ($terminalType) {
                $query->where(['p.terminal_type' => 0])->orWhere('p.terminal_type', 'like', '%' . $terminalType . '%');
            });
        });

        //排序
        $query->orderBy('p.position_sort', 'asc')->orderBy('p.platform_product_id', 'desc');

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
     * 不想看产品标签流水记录
     * @param array $params
     * @return bool
     */
    public static function createProductBlackTagLog($params = [])
    {
        $log = new UserProductBlackTagLog();
        $log->user_id = $params['userId'];
        $log->product_id = $params['productId'];
        $log->tag_id = isset($params['tagId']) ? 0 : $params['tagId'];
        $log->content = isset($params['content']) ? $params['content'] : '';
        $log->type = $params['type'];
        $log->status = 0;
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();

        $res = $log->save();

        return $res;
    }

    /**
     * 删除标签
     * @param array $params
     * @return bool
     */
    public static function deletePeoductBlackTags($params = [])
    {
        $deleteTag = UserProductBlackTag::select(['id'])
            ->where(['user_id' => $params['userId'], 'product_id' => $params['productId']])
            ->update([
                'status' => 9,
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);

        return $deleteTag ? $deleteTag : false;
    }

    /**
     * 不想看产品对应标签
     * @param array $params
     * @return array
     */
    public static function fetchBlackConTagIds($params = [])
    {
        $conTagIds = UserProductBlackTag::select(['id', 'tag_id'])
            ->where(['user_id' => $params['userId'], 'product_id' => $params['productId']])
            ->pluck('tag_id')
            ->toArray();

        return $conTagIds ? $conTagIds : [];
    }

    /**
     * 不想看产品标签修改
     * @param array $params
     * @return bool
     */
    public static function updateProductBlackTag($params = [])
    {
        $tag = UserProductBlackTag::select(['id'])
            ->where(['user_id' => $params['userId'], 'product_id' => $params['productId'], 'tag_id' => $params['tagId']])
            ->first();

        if (!$tag) {
            $tag = new UserProductBlackTag();
            $tag->created_at = date('Y-m-d H:i:s', time());
            $tag->created_ip = Utils::ipAddress();
        }

        $tag->user_id = $params['userId'];
        $tag->product_id = $params['productId'];
        $tag->tag_id = isset($params['tagId']) ? $params['tagId'] : 0;
        $tag->content = isset($params['content']) ? $params['content'] : '';
        $tag->type = $params['type'];
        $tag->status = isset($params['status']) ? $params['status'] : 0;
        $tag->updated_at = date('Y-m-d H:i:s', time());
        $tag->updated_ip = Utils::ipAddress();

        return $tag->save();
    }

    /**
     * 详情页标签类型 获取id
     * @param string $typeNid
     * @return int
     */
    public static function fetchProductTagTypeIdByNid($typeNid = '')
    {
        $typeId = PlatformProductTagType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $typeId ? $typeId->id : 0;
    }

    /**
     * 根据标签类型id查询标签ids
     * @param string $typeId
     * @return array
     */
    public static function fetchProductTagsByTagId($typeId = '')
    {
        $tagIds = ProductTag::select(['tag_id'])
            ->where(['type_id' => $typeId, 'status' => 1])
            ->pluck('tag_id')
            ->toArray();

        return $tagIds ? $tagIds : [];
    }

    /**
     * 根据标签ids获取标签数据
     * @param array $ids
     * @return array
     */
    public static function fetchSeoTagsByIds($ids = [])
    {
        $tags = TagSeo::select(['id', 'name', 'font_color', 'boder_color', 'bg_color'])
            ->whereIn('id', $ids)
            ->get()
            ->toArray();

        return $tags ? $tags : [];
    }
}

