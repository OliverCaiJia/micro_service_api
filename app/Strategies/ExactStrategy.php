<?php

namespace App\Strategies;

use App\Helpers\DateUtils;
use App\Helpers\Utils;
use App\Models\ComModelFactory;
use App\Services\Core\Store\Qiniu\QiniuService;

class ExactStrategy extends AppStrategy
{
    /**
     * @param $exactBanner
     * @return mixed
     * 精确匹配 —— 广告图片
     */
    public static function getExactBanner($exactBanner)
    {
        $exact['exact_img'] = !empty($exactBanner['exact_img']) ? QiniuService::getImgs($exactBanner['exact_img']) : '';

        return $exact;
    }

    /**
     * @param $certify
     * @param $identity
     * @param $profile
     * @return array
     * 精确匹配所需用户信息数据处理
     */
    public static function getExactUserinfo($certify, $identity, $profile)
    {
        if ($certify && $identity && $profile) {
            $user = array_merge($certify, $identity, $profile);
            foreach ($user as $key => $val) {
                if (is_string($val) && $val) {
                    $user[$key] = 1;
                }
            }
        } else {
            $user = [];
        }

        return $user;
    }

    /**
     * @param $fieldArr
     * @return array
     * 精确匹配产品信息数据处理
     */
    public static function getMatchProdectinfo($fieldArr)
    {
        $productData = [];
        foreach ($fieldArr as $dk => $dv) {
            if ($dv['productvalue'] && $dv['productId']) {
                foreach ($dv['productvalue'] as $key => $val) {
                    //必要条件
                    $productData[$dk][$key]['productId'] = $dv['productId'];
                    $productData[$dk][$key]['productname'] = $dv['productname'];
                    $productData[$dk][$key]['product_property_filed_id'] = $val['product_property_filed_id'];
                    $productData[$dk][$key]['parent_id'] = $val['parent_id'];
                    $productData[$dk][$key]['score'] = $val['score'];
                    $productData[$dk][$key]['value'] = $val['value'];
                    $productData[$dk][$key]['name'] = $val['name'];
                    $productData[$dk][$key]['is_necessity'] = $val['is_necessity'];
                    $productData[$dk][$key]['short_name'] = $val['short_name'];
                }
            }
        }

        return $productData;
    }

    /**
     * @param $user
     * @param $productinfo
     * @return array
     * 精确匹配 用户数据与产品数据 进行对比
     */
    public static function getExactMatchData($user, $productinfo)
    {
        //print_r($user);die();
        //获取产品的匹配条件
        $productMatchCondition = ExactStrategy::getProductMatchCondition($productinfo);
        //print_r($productMatchCondition);die();

        //获取产品必要条件 & 不必要条件的总个数
        $productMatchConditionCount = ExactStrategy::getProductMatchConditionCount($productMatchCondition);
        //print_r($productMatchConditionCount);die();

        //取得用户信息中的必要属性 不必要属性 对比两个数组的键与值   $user 与 $productMatchCondition
        //对比结果 取到必须匹配的值 不必须匹配的值 $matchResultArr
        $matchResultArr = ExactStrategy::getProductMatchUser($user, $productMatchCondition);
        //print_r($matchResultArr);die();

        //计算必要属性个数  不必要属性个数
        //存放必要属性的值 $matchResultCountArr
        $matchResultCountArr = ExactStrategy::getProductMatchUserCount($matchResultArr);
        //print_r($matchResultCountArr);die();


        //计算符合的必须匹配的个数  不必须匹配的个数  $matchResultCounts
        $matchResultCounts = ExactStrategy::getProductMatchUserCounts($matchResultCountArr);
        //print_r($matchResultCounts);die;


        //匹配的最终结果   得到匹配产品的id   $mathArr
        $mathArr = ExactStrategy::getExactMatchResult($matchResultCounts, $productMatchConditionCount);
        //print_r($mathArr);die;

        return $mathArr ? $mathArr : [];
    }

    /**
     * @param $productinfo
     * 获取产品的匹配条件
     */
    public static function getProductMatchCondition($productinfo)
    {
        $productMatchCondition = [];
        foreach ($productinfo as $key => $val) {
            foreach ($val as $k => $v) {
                $productMatchCondition[$key][$k][$v['short_name']] = $v['value'];
                $productMatchCondition[$key][$k]['is_necessity'] = $v['is_necessity'];
                $productMatchCondition[$key][$k]['productId'] = $v['productId'];
            }
        }

        return $productMatchCondition;
    }

    /**
     * @param $productMatchCondition
     * 获取产品必要条件 & 不必要条件的总个数
     */
    public static function getProductMatchConditionCount($productMatchCondition)
    {
        //获取产品必要属性的个数   $productRes
        //不必要属性的个数   $UnproductRes
        $necessCountArr = [];
        $unnecessCountArr = [];
        foreach ($productMatchCondition as $key => $val) {
            foreach ($val as $k => $v) {
                if ($v['is_necessity'] == 1) {
                    $necessCountArr[$key]['necesscount'] = count($v) - 1;
                    $necessCountArr[$key]['productId'] = $v['productId'];
                    $necessCountArr[$key]['unnececount'] = 0;
                } else {
                    $unnecessCountArr[$key]['unnececount'] = count($v);
                    $unnecessCountArr[$key]['productId'] = $v['productId'];
                    $unnecessCountArr[$key]['necesscount'] = 0;

                }
            }
        }

        //合并两个数组
        foreach ($necessCountArr as $key => $val) {
            $necessCountArr[$key]['unnececount'] = isset($unnecessCountArr[$key]['unnececount']) ? $unnecessCountArr[$key]['unnececount'] : 0;
        }

        return $necessCountArr ? $necessCountArr : [];
    }

    /**
     * @param $user
     * @param $productMatchCondition
     * //取得用户信息中的必要属性 不必要属性 对比两个数组的键与值   $user 与 $productMatchCondition
     * //对比结果 取到必须匹配的值 不必须匹配的值 $matchResultArr
     */
    public static function getProductMatchUser($user, $productMatchCondition)
    {
        $matchResultArr = [];
        foreach ($productMatchCondition as $key => $val) {
            foreach ($val as $k => $v) {
                $matchResultArr[$key]['productId'] = $v['productId'];
                if ($v['is_necessity'] == 1) {
                    $matchResultArr[$key][$k]['necessary'] = array_intersect_assoc($v, $user);
                } else {
                    $matchResultArr[$key][$k]['unnecessary'] = array_intersect_assoc($v, $user);
                }
            }
        }

        return $matchResultArr;
    }

    /**
     * @param $matchResultArr
     * //计算必要属性个数  不必要属性个数
     * //存放必要属性的值 $matchResultCountArr
     */
    public static function getProductMatchUserCount($matchResultArr)
    {
        $neceCountArr = [];
        //存放不必须匹配的个数 $neceCountArr
        $unneceCountArr = [];
        foreach ($matchResultArr as $key => $val) {
            foreach ($val as $k => $v) {
                if (is_array($v)) {
                    if (isset($v['necessary'])) {
                        $nece = $v['necessary'];
                        $neceCountArr[$key][$k] = count($nece);
                    } else {
                        $unnece = $v['unnecessary'];
                        $unneceCountArr[$key][$k] = count($unnece);
                    }
                } else {
                    $neceCountArr[$key]['productId'] = $val['productId'];
                }
            }
        }

        $data['neceCount'] = $neceCountArr;
        $data['unneceCount'] = $unneceCountArr;

        return $data;
    }

    /**
     * @param $matchResultCountArr
     * 计算符合的必须匹配的个数  不必须匹配的个数  $matchResultCounts
     */
    public static function getProductMatchUserCounts($matchResultCountArr)
    {
        $neceArr = $matchResultCountArr['neceCount'];
        $unneceArr = $matchResultCountArr['unneceCount'];

        $res = [];
        foreach ($neceArr as $key => $val) {
            $res[$key]['productId'] = $val['productId'];
            unset($val['productId']);
            $res[$key]['necesscount'] = array_sum($val);  //必须匹配的个数
            if ($unneceArr) {
                foreach ($unneceArr as $k => $v) {
                    if ($key == $k) {
                        $res[$key]['unnececount'] = array_sum($v);
                    } else {
                        $res[$key]['unnececount'] = 0;
                    }
                }
            } else {
                $res[$key]['unnececount'] = 0;
            }

        }

        return $res;
    }

    /**
     * @param $matchResultCounts
     * 匹配的最终结果   得到匹配产品的id   $mathArr
     */
    public static function getExactMatchResult($matchResultCounts, $productMatchConditionCount)
    {
        //print_r($matchResultCounts);
        //print_r($productMatchConditionCount);die();

        $pipei = [];
        foreach ($matchResultCounts as $mk => $mv) {
            foreach ($productMatchConditionCount as $pk => $pv) {
                if ($pv['productId'] == $mv['productId'] && $mv['necesscount'] == $pv['necesscount']) {  //必须匹配满足
                    //不必须匹配的个数比较
                    $unne = $pv['unnececount'] - $mv['unnececount'];
                    if ($unne < 0) {
                        $unne = $mv['unnececount'] - $pv['unnececount'];
                    }
                    $unnecount = $unne;
                    $pipei[$mk]['productId'] = $mv['productId'];
                    $pipei[$mk]['unnececount'] = $unnecount;
                }

            }
        }
        //精确匹配的产品
        return $pipei;
    }

    /**
     * 精确匹配结果数据处理
     * @param $productArr
     * @param $matchinfoArr
     * @return array
     */
    public static function getExactMatchDatas($productArr, $matchinfoArr)
    {
        $collection = collect($matchinfoArr);
        $unnececountArr = $collection->keyBy('productId')->toArray();

        //将不必要条件放入产品数组中
        foreach ($productArr as $k => $v) {
            $productArr[$k]['unnecount'] = $unnececountArr[$v['platform_product_id']]['unnececount'];
        }

        $productData = [];
        foreach ($productArr as $key => $val) {
            $productData[$key]['unnecount'] = $val['unnecount'];
            $productData[$key]['star'] = $val['satisfaction'] . '';
            $productData[$key]['platform_product_id'] = $val['platform_product_id'];
            $productData[$key]['platform_id'] = $val['platform_id'];
            $productData[$key]['platform_product_name'] = $val['platform_product_name'];
            $productData[$key]['product_introduct'] = Utils::removeHTML($val['product_introduct']);
            $productData[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
            $productData[$key]['tag_name'] = isset($val['tag_name']) ? $val['tag_name'] : [];
            $productData[$key]['success_count'] = DateUtils::ceilMoney($val['success_count']);
            $productData[$key]['fast_time'] = ProductStrategy::fetchFastTime($val['value']);
            $productData[$key]['productType'] = 1;
        }

        if ($productData) {
            sort($productData);
        }

        return $productData ? $productData : [];
    }

    /**
     * 第二版  精确匹配数据处理
     * @param $productArr
     * @param $matchinfoArr
     * @param array $data
     * @return array
     */
    public static function getSecondEditionExactMatchDatas($productArr, $matchinfoArr, $data = [])
    {
        $collection = collect($matchinfoArr);
        $unnececountArr = $collection->keyBy('productId')->toArray();

        //将不必要条件放入产品数组中
        foreach ($productArr as $k => $v) {
            $productArr[$k]['unnecount'] = $unnececountArr[$v['platform_product_id']]['unnececount'];
        }

        $productData = [];
        foreach ($productArr as $key => $val) {
            $productData[$key]['unnecount'] = $val['unnecount'];
            $productData[$key]['star'] = $val['satisfaction'] . '';
            $productData[$key]['platform_product_id'] = $val['platform_product_id'];
            $productData[$key]['platform_id'] = $val['platform_id'];
            $productData[$key]['platform_product_name'] = $val['platform_product_name'];
            $productData[$key]['product_introduct'] = Utils::removeHTML($val['product_introduct']);
            $productData[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
            $productData[$key]['tag_name'] = $val['tag_name'];
            $todayTotalCount = bcadd($val['total_today_count'], 0);
            //$productData[$key]['success_count'] = DateUtils::ceilMoney($val['success_count']);
            $productData[$key]['success_count'] = DateUtils::ceilMoney($todayTotalCount);
            //今日申请总数
            $productData[$key]['total_today_count'] = DateUtils::ceilMoney($todayTotalCount);
            //额度
            $productData[$key]['interest_alg'] = $val['interest_alg'];
            $loan_min = DateUtils::formatIntToThous($val['loan_min']);
            $loan_max = DateUtils::formatIntToThous($val['loan_max']);
            $productData[$key]['quota'] = $loan_min . '~' . $loan_max;
            //期限
            $period_min = ProductStrategy::formatDayToMonthByInterestalg($val['interest_alg'], $val['period_min']);
            $period_max = ProductStrategy::formatDayToMonthByInterestalg($val['interest_alg'], $val['period_max']);
            $productData[$key]['term'] = $period_min . '~' . $period_max;
            $productData[$key]['productType'] = 1;

            //日、月利息
            $productData[$key]['interest_rate'] = $val['min_rate'] . '%';
            //下款时间
            $loanSpeed = empty($val['value']) ? '3600' : $val['value'];
            $productData[$key]['loan_speed'] = ProductStrategy::formatLoanSpeed($loanSpeed) . '小时';
            //是否是速贷优选产品
            $productData[$key]['is_preference'] = isset($val['is_preference']) ? $val['is_preference'] : 0;
            //加密手机号
            $productData[$key]['mobile'] = ProductStrategy::fetchEncryptMobile($data);
            //对接标识
            $productData[$key]['type_nid'] = isset($val['type_nid']) ? strtolower($val['type_nid']) : '';
        }

        if ($productData) {
            sort($productData);
        }

        return $productData ? $productData : [];
    }
}