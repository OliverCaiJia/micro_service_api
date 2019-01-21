<?php

namespace App\Strategies;

use App\Constants\ProductConstant;
use App\Helpers\DateUtils;
use App\Helpers\LinkUtils;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Models\ComModelFactory;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\ProductFactory;
use App\Models\Factory\ProductPropertyFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * 产品公共策略
 *
 * Class UserStrategy
 * @package App\Strategies
 */
class ProductStrategy extends AppStrategy
{
    /**
     * @param $product
     * @return \stdClass
     * 返回首页诱导轮播产品数据
     */
    public static function getPromotions($product, $applyPeoples, $register = 0)
    {
        foreach ($product as $key => $val) {
            $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
        }
        $productArr['list'] = $product ? $product : RestUtils::getStdObj();
        $productArr['apply_num'] = bcadd($applyPeoples, $register);
        return $productArr;
    }

    /**
     * @param $productTag
     * 返回速贷攻略数据
     */
    public static function getGuides($productTag, $pageCount)
    {
        foreach ($productTag as $key => $val) {
            $productTag[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
        }
        $product['list'] = $productTag ? $productTag : RestUtils::getStdObj();
        $product['pageCount'] = $pageCount;

        return $product;
    }

    /**
     * 返回最新产品图片
     * @param $product
     * @param string $onlineConfigValue
     * @return array
     */
    public static function getNewsOlines($product, $onlineConfigValue = '')
    {
        foreach ($product as $key => $val) {
            $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
            $product[$key]['product_introduct'] = Utils::removeHTML($val['product_introduct']);
            $product[$key]['product_conduct'] = $onlineConfigValue;
            $product[$key]['update_date'] = DateUtils::formatDataToDay($val['create_date']);
        }
        return $product ? $product : [];
    }

    /**
     * 返回分类专题对应产品数据
     * @param $specialIds
     * @param $specialLists
     * @param $pageCount
     * @param $productType
     * @return mixed
     */
    public static function getSpecials($specialIds, $specialLists, $pageCount, $productType)
    {
        $result = [];
        $productIdArr = explode(',', $specialIds['product_list']);
        foreach ($productIdArr as $key => $value) {
            foreach ($specialLists as $k => $val) {
                if ($value == $val['platform_product_id']) {
                    $result[$key] = $val;
                    $result[$key]['productType'] = $productType;
                }
            }
        }

        //处理图片 
        foreach ($result as $rkey => $rval) {
            $result[$rkey]['product_logo'] = QiniuService::getProductImgs($rval['product_logo'], $rval['platform_product_id']);
            $result[$rkey]['success_count'] = DateUtils::ceilMoney($rval['success_count']);
            if ($productType == 5) {
                $result[$rkey]['star'] = $rval['loan_max'];
            } else {
                $result[$rkey]['star'] = bcadd($rval['avg_quota'], 0);
            }
            $result[$rkey]['product_introduct'] = Utils::removeHTML($rval['product_introduct']);
            $result[$rkey]['avg_quota'] = bcadd($rval['avg_quota'], 0);
            $result[$rkey]['fast_time'] = self::fetchFastTime($rval['value']);

        }
        $result = array_values($result);
        $datas['list'] = $result ? $result : RestUtils::getStdObj();
        $datas['pageCount'] = $pageCount;
        $datas['title'] = isset($specialIds['title']) ? $specialIds['title'] : '';
        $datas['img'] = isset($specialIds['img']) ? QiniuService::getImgs($specialIds['img']) : '';
        return $datas;
    }

    /**
     * 第二版 分类专题产品数据个数处理
     * @param array $params
     * @return mixed
     */
    public static function getSpecialLists($params = [])
    {
        $specialIds = $params['specialIds'];
        $specialLists = $params['specialLists'];
        $productType = $params['productType'];

        $result = [];
        $productIdArr = explode(',', $specialIds['product_list']);
        foreach ($productIdArr as $key => $value) {
            foreach ($specialLists as $k => $val) {
                if ($value == $val['platform_product_id']) {
                    $result[$key] = $val;
                    $result[$key]['productType'] = $productType;
                }
            }
        }

        //处理图片
        $product = [];
        foreach ($result as $rkey => $rval) {
            $product[$rkey]['platform_product_id'] = $rval['platform_product_id'];
            $product[$rkey]['platform_id'] = $rval['platform_id'];
            $product[$rkey]['platform_product_name'] = $rval['platform_product_name'];
            $product[$rkey]['product_logo'] = QiniuService::getProductImgs($rval['product_logo'], $rval['platform_product_id']);
            $product[$rkey]['tag_name'] = $rval['tag_name'];
            $successCount = bcadd($rval['success_count'], 0);
            $todayTotalCount = bcadd($rval['total_today_count'], 0);
            $product[$rkey]['success_count'] = DateUtils::ceilMoney($todayTotalCount);
            //今日申请总数
            $product[$rkey]['total_today_count'] = DateUtils::ceilMoney($todayTotalCount);
            $product[$rkey]['product_introduct'] = ComModelFactory::escapeHtml($rval['product_introduct']);
            //额度
            $product[$rkey]['interest_alg'] = $rval['interest_alg'];
            $loan_min = DateUtils::formatIntToThous($rval['loan_min']);
            $loan_max = DateUtils::formatIntToThous($rval['loan_max']);
            $product[$rkey]['quota'] = $loan_min . '~' . $loan_max;
            //期限
            $period_min = ProductStrategy::formatDayToMonthByInterestalg($rval['interest_alg'], $rval['period_min']);
            $period_max = ProductStrategy::formatDayToMonthByInterestalg($rval['interest_alg'], $rval['period_max']);
            $product[$rkey]['term'] = $period_min . '~' . $period_max;
            $product[$rkey]['productType'] = intval($productType);
            //日、月利息
            $product[$rkey]['interest_rate'] = $rval['min_rate'] . '%';
            //下款时间
            $loanSpeed = empty($rval['value']) ? '3600' : $rval['value'];
            $product[$rkey]['loan_speed'] = ProductStrategy::formatLoanSpeed($loanSpeed) . '小时';
            //是否是速贷优选产品
            $product[$rkey]['is_preference'] = isset($rval['is_preference']) ? $rval['is_preference'] : 0;
            //加密手机号
            $product[$rkey]['mobile'] = ProductStrategy::fetchEncryptMobile($params);
            //对接标识
            $product[$rkey]['type_nid'] = $rval['type_nid'] ? strtolower($rval['type_nid']) : '';
        }
        $result = array_values($product);
        $datas['list'] = $result ? $result : RestUtils::getStdObj();
        $datas['pageCount'] = $params['pageCount'];
        $datas['title'] = isset($specialIds['title']) ? $specialIds['title'] : '';
        $datas['img'] = isset($specialIds['img']) ? QiniuService::getImgs($specialIds['img']) : '';
        return $datas;
    }

    /**
     * @param int $type
     * @param array $product
     * @param int $countPage
     * @return mixed
     * 全部产品详情
     */
    public static function productAll($type = 1, $product = [], $countPage = 0)
    {
        $dataAll = [];
        switch ($type) {
            case 1:     //定位排序
                foreach ($product as $key => $val) {
                    $product[$key]['position_sort'] = $val['position_sort'];
                    $rate = $val['composite_rate'];
                    $speed = $val['loan_speed'];
                    $exper = $val['experience'];
                    $star = bcdiv(bcadd($exper, bcadd($rate, $speed)), 3, 1);
                    $product[$key]['star'] = !empty($star) ? $star : '4.0';
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 2:     //成功率
                foreach ($product as $key => $val) {
                    $star = bcadd($val['success_rate'], 0);
                    $product[$key]['star'] = $star;
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 3:     //放款速度
                foreach ($product as $key => $val) {
                    $star = bcadd($val['loan_speed'], 0, 1);
                    $product[$key]['star'] = $star;
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 4:     //贷款利率
                foreach ($product as $key => $val) {
                    $star = bcadd($val['composite_rate'], 0, 1);
                    $product[$key]['star'] = $star;
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 5:     //最高额度
                foreach ($product as $key => $val) {
                    $star = bcadd($val['loan_max'], 0);
                    $product[$key]['star'] = $star;
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 6:     //新上线产品
                foreach ($product as $key => $val) {
                    $rate = $val['composite_rate'];
                    $speed = $val['loan_speed'];
                    $exper = $val['experience'];
                    $star = bcdiv(bcadd($exper, bcadd($rate, $speed)), 3, 1);
                    $product[$key]['star'] = $star;
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 7:     //平均额度
                foreach ($product as $key => $val) {
                    $product[$key]['star'] = bcadd($val['avg_quota'], 0);
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 8:     //新放款速度
                foreach ($product as $key => $val) {
                    $star = ProductStrategy::formatLoanSpeedToStar($val['value']);
                    $product[$key]['star'] = $star;
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
        }

        return $dataAll;
    }

    /**
     * @param string $fastTime
     * @return string
     * 平均放款时间
     */
    public static function fetchFastTime($fastTime = 3600)
    {
        //转化为小时
        $loanSpeed = bcdiv($fastTime, 3600, 1);
        return '平均' . $loanSpeed . '小时放款';
    }

    /**
     * @param int $fastTime
     * @return string
     * 平均放款时间
     */
    public static function formatFastTime($fastTime = 3600)
    {
        //转化为小时
        $loanSpeed = bcdiv($fastTime, 3600, 1);
        return $loanSpeed ? $loanSpeed : '';
    }

    /**
     * @param $product
     * 返回信用卡数据
     */
    public static function getCreditCards($creditcardArr)
    {
        foreach ($creditcardArr as $key => $val) {
            $count = bcadd($val['loan_speed'], bcadd($val['composite_rate'], $val['experience']));
            $creditcardArr[$key]['star'] = bcdiv($count, 3, 1);                                                  //综合指数
            $creditcardArr[$key]['comment_count'] = CommentFactory::commentAll($val['platform_product_id']);               //评论次数
            $creditcardArr[$key]['success_count'] = DateUtils::formatMoney($val['success_count']);                         //成功申请次数
            $creditcardArr[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
        }
        return $creditcardArr;
    }

    /**
     * @param $calcuLists
     * 返回计算器数据
     */
    public static function getCalculators($productArr)
    {
        //利率
        $lilv = self::intRateStr($productArr['interest_alg']);
        $productArr['interest_alg_num'] = $productArr['interest_alg'];
        $productArr['interest_alg'] = $lilv;
        $productArr['interest_rate'] = self::rateValue($productArr['min_rate']);
        $productArr['avg_quota'] = empty($productArr['avg_quota']) ? '' : $productArr['avg_quota'];
        //每期利息 || 一次性还款
        $productArr['pay_method'] = self::interestPay($productArr['pay_method']);
        if ($productArr['pay_method'] == 1) {
            $productArr['method'] = ProductConstant::REPAYMENT;
        } else {
            $productArr['method'] = ProductConstant::EACH_INTEREST;
        }
        //计算的最终结果
        //$datas['calculatedValue']   = Product::calculateRate($productArr,$data);
        //金额

        return $productArr;
    }

    /**
     * @param $loanMax
     * 通过产品最高金额与最高金额   获取对应的金额金额
     */
    public static function getMoneyData($productArr)
    {
        //截取数组
        $calculatorMoney = ProductConstant::CALCULATOR_MONEY;
        $calculatorMoneyInt = ProductConstant::CALCULATOR_MONEY_INT;

        //比较两个数组
        $min = ProductStrategy::getMin($calculatorMoneyInt, $productArr['loan_min'], 1);
        $max = ProductStrategy::getMax($calculatorMoneyInt, $productArr['loan_max'], 1);

        return ProductStrategy::getSliceDatas($calculatorMoney, $min, $max);
    }

    /**
     * @param $productArr
     * @return array
     * 通过产品最高金额与最高期限   获取对应的期限范围
     */
    public static function getTermData($productArr)
    {
        $calculatorTerm = ProductConstant::CALCULATOR_TERM;
        $calculatorTermInt = ProductConstant::CALCULATOR_TERM_INT;

        //比较两个数组
        $min = ProductStrategy::getMin($calculatorTermInt, $productArr['period_min'], 2);
        $max = ProductStrategy::getMax($calculatorTermInt, $productArr['period_max'], 2);
        return ProductStrategy::getSliceDatas($calculatorTerm, $min, $max);
    }

    /**
     * 第二版 计算器期限只显示 日/月
     * @param array $productArr
     * @return array
     */
    public static function getSecondEditionTermData($productArr = [])
    {
        $calculatorTerm = ProductConstant::SECOND_EDITION__CALCULATOR_TIME;
        $calculatorTermInt = ProductConstant::CALCULATOR_TERM_INT;

        //比较两个数组
        $min = ProductStrategy::getMinData($calculatorTermInt, $productArr['period_min'], 2);
        $max = ProductStrategy::getMaxData($calculatorTermInt, $productArr['period_max'], 2);
        return ProductStrategy::getSliceDatas($calculatorTerm, $min, $max);
    }

    /**
     * 常量与数组对比  取最小值
     * @param array $params
     * @param $param
     * @param $type
     * @return string
     */
    public static function getMin($params = [], $param, $type)
    {
        $min = 0;
//        foreach ($params as $calKey => $calValue) {
//            if ($param <= $params[0]) {
//                $min = $params[0];
//            } elseif ($param > $params[$calKey] && $param <= $params[$calKey + 1]) {
//                $min = $params[$calKey + 1];
//            }else {
//                $min = $params[$calKey];
//            }
//        }
        $min = array_reduce($params, function ($v, $w) use ($param) {
            $v = $v >= $param ? $v : $w;
            return $v;
        });

        switch ($type) {
            case 1: //金额
                return DateUtils::formatMoneyToInt($min);
                break;
            case 2://期限
                return DateUtils::formatTimeToYear($min);
                break;
        }
    }

    /**
     *
     * @param array $params
     * @param $param
     * @param $type
     * @return string
     */
    public static function getMinData($params = [], $param, $type)
    {
        $min = 0;
        $min = array_reduce($params, function ($v, $w) use ($param) {
            $v = $v >= $param ? $v : $w;
            return $v;
        });

        switch ($type) {
            case 1: //金额
                return DateUtils::formatMoneyToInt($min);
                break;
            case 2://期限
                return DateUtils::formatTimeToMonth($min);
                break;
        }
    }

    /**
     * @param array $params
     * @param $param
     * @param $type
     * @return string
     * 常量与数组对比  取最大值
     */
    public static function getMax($params = [], $param, $type)
    {
        $max = 0;
        foreach ($params as $calKey => $calValue) {
            if ($param <= $params[0]) {
                $max = $params[0];
            } elseif ($param >= $params[$calKey]) {
                $max = $params[$calKey];
            }
        }

        switch ($type) {
            case 1: //金额
                return $max = DateUtils::formatMoneyToInt($max);
                break;
            case 2://期限
                return $max = DateUtils::formatTimeToYear($max);
                break;
        }

    }

    /**
     * 常量与数组对比  取最大值
     * @param array $params
     * @param $param
     * @param $type
     * @return string
     */
    public static function getMaxData($params = [], $param, $type)
    {
        $max = 0;
        foreach ($params as $calKey => $calValue) {
            if ($param <= $params[0]) {
                $max = $params[0];
            } elseif ($param >= $params[$calKey]) {
                $max = $params[$calKey];
            }
        }

        switch ($type) {
            case 1: //金额
                return $max = DateUtils::formatMoneyToInt($max);
                break;
            case 2://期限
                return $max = DateUtils::formatTimeToMonth($max);
                break;
        }

    }

    /**
     * @param array $params
     * @param $min
     * @param $max
     * @return array
     * 截取数组长度
     */
    public static function getSliceDatas($params = [], $min, $max)
    {
        $minKey = array_search($min, $params);
        $maxKey = array_search($max, $params);
        $minArr = array_slice($params, $minKey);
        $maxArr = array_slice($params, $maxKey + 1);
        $data = array_diff($minArr, $maxArr);
        return $data ? $data : [$min];
    }


    /**
     * @param $product
     * 返回产品详情数据
     */
    public static function getDetails($productTag, $productId)
    {
        $product = [];
        $product['platform_product_id'] = $productTag['platform_product_id'];
        $product['platform_id'] = $productTag['platform_id'];
        $product['platform_product_name'] = isset($productTag['platform_product_name']) ? $productTag['platform_product_name'] : '';
        $product['product_logo'] = QiniuService::getProductImgs($productTag['product_logo'], $productTag['platform_product_id']);
        $product['tag_name'] = isset($productTag['tag_name']) ? $productTag['tag_name'] : [];
        $product['platform_name'] = isset($productTag['platform_name']) ? $productTag['platform_name'] : '';
        //申请人评价
        $successCount = bcadd($productTag['success_count'], 0);
        $product['success_num'] = self::successCompare($successCount);
        $product['success_width'] = self::imgWidth($product['success_num']);
        $product['success_count'] = DateUtils::ceilMoney($successCount);
        $product['fast_num'] = self::fastCompare($productTag['loan_speed']);
        $product['fast_width'] = self::imgWidth($product['fast_num']);
        $product['fast_time'] = isset($productTag['fast_time']) ? $productTag['fast_time'] : 0;
        $successRate = ProductFactory::passRate($productTag['platform_product_id']);
        $product['pass_num'] = self::passCompare($successRate);
        $product['pass_width'] = self::imgWidth($product['pass_num']);
        $product['comment_count'] = CommentFactory::commentAllCount($productId);
        //广告图片
        $product['banner_img'] = QiniuService::getImgs($productTag['banner_img']);
        //申请条件
        $product['apply_condition'] = isset($productTag['apply_condition']) ? ComModelFactory::escapeHtml($productTag['apply_condition']) : '';
        //申请流程
        $product['process'] = ProductFactory::applicationProcess($productTag['apply_process_ids']);
        //借款细节
        $product['loan_detail'] = self::loanDetail($productTag);
        //新手指导
        $product['guide'] = isset($productTag['guide']) ? ComModelFactory::escapeHtml($productTag['guide']) : '';
        //产品优势
        $product['product_introduct'] = isset($productTag['product_introduct']) ? ComModelFactory::escapeHtml($productTag['product_introduct']) : '';
        //查看攻略
        $product['news_link'] = isset($productTag['news_link']) ? $productTag['news_link'] : '';
        //分享链接
        $product['h5_link'] = LinkUtils::productShare($productId);
        //申请借款开关判断
        $product['apply_button_text_flag'] = 'v2.5.1';
        $product['apply_button_text'] = '查看攻略';
        //print_r($product);die();
        return $product;
    }

    /**
     * @param $productTag
     * @param $productId
     * 产品详情  第一部分
     */
    public static function getDetail($productTag, $productId = '', $loanSpeed = '')
    {
        $product = [];
        $product['platform_product_id'] = $productTag['platform_product_id'];
        $product['platform_id'] = $productTag['platform_id'];
        $product['platform_product_name'] = isset($productTag['platform_product_name']) ? $productTag['platform_product_name'] : '';
        $product['product_logo'] = QiniuService::getProductImgs($productTag['product_logo'], $productTag['platform_product_id']);
        $product['tag_name'] = isset($productTag['tag_name']) ? $productTag['tag_name'] : [];
        $product['platform_name'] = isset($productTag['platform_name']) ? $productTag['platform_name'] : '';
        //申请人评价
        //产品人气
        $successCount = bcadd($productTag['success_count'], 0);
        $product['success_num'] = self::formatSuccessCompare($successCount);
        $product['success_width'] = self::imgWidth($product['success_num']);
        $product['success_count'] = DateUtils::ceilMoney($successCount);
        //放款速度
        $product['fast_num'] = self::formatFast($loanSpeed);
        $product['fast_width'] = self::imgWidth($product['fast_num']);
        $product['fast_time'] = isset($productTag['fast_time']) ? $productTag['fast_time'] : 0;
        $product['fast_speed'] = self::formatLoanSpeed($loanSpeed);
        //下款概率
        $product['pass_num'] = self::formatPassCompare($productTag['success_rate']);
        $product['pass_width'] = self::imgWidth($product['pass_num']);
        $product['comment_count'] = CommentFactory::commentAllCount($productId);
        $product['pass_rate'] = DateUtils::formatPercentage($productTag['success_rate']);
        //广告图片
        $product['banner_img'] = QiniuService::getImgs($productTag['banner_img']);
        //查看攻略
        $product['news_link'] = isset($productTag['news_link']) ? $productTag['news_link'] : '';
        //分享链接
        $product['h5_link'] = LinkUtils::productShare($productId);
        //申请借款开关判断
        $product['apply_button_text_flag'] = 'v2.5.1';
        $product['apply_button_text'] = '查看攻略';
        //评论总数
        $product['commentCounts'] = CommentStrategy::getCommentCounts($productTag['commentCounts']);

        return $product;
    }

    /**
     * @param $productTag
     * @param $productId
     * 产品详情细节  第二部分
     */
    public static function getProductDetails($productInfo, $productId)
    {
        $product = [];
        $product['platform_product_id'] = $productInfo['platform_product_id'];
        $product['platform_id'] = $productInfo['platform_id'];
        $product['platform_product_name'] = isset($productInfo['platform_product_name']) ? $productInfo['platform_product_name'] : '';
        //申请条件
        $product['apply_condition'] = isset($productInfo['apply_condition']) ? ComModelFactory::escapeHtml($productInfo['apply_condition']) : '';
        //申请流程
        $product['process'] = ProductFactory::applicationProcess($productInfo['apply_process_ids']);
        //借款细节
        $product['loan_detail'] = self::loanDetail($productInfo);
        //新手指导
        $product['guide'] = isset($productInfo['guide']) ? ComModelFactory::escapeHtml($productInfo['guide']) : '';
        //产品优势
        $product['product_introduct'] = isset($productInfo['product_introduct']) ? ComModelFactory::escapeHtml($productInfo['product_introduct']) : '';
        return $product;
    }

    /**
     * @param $data
     * @return string
     * @desc    通过概率转化  （已授信人数+已放款人数）/（已授信人数+已放款人数+被拒绝的人数）
     * api4
     */
    public static function passCompare($data)
    {
        $i = floatval($data);
        if ($i <= 0.1) return ProductConstant::COUHE;
        elseif ($i >= 0.11 && $i <= 0.2) return ProductConstant::ORDINARY;
        elseif ($i >= 0.21 && $i <= 0.4) return ProductConstant::GOOD;
        elseif ($i >= 0.41 && $i <= 0.6) return ProductConstant::BETTER;
        elseif ($i >= 0.61 && $i <= 0.8) return ProductConstant::BEST;
        elseif ($i >= 0.81 && $i <= 1) return ProductConstant::EXCELLENT;
        else return '';
    }

    /**
     * @param $data
     * @return string
     * @desc    通过概率转化  （已批贷）/（已批贷+被拒绝+等待审批）
     * api4
     */
    public static function formatPassCompare($data)
    {
        $i = floatval($data);
        if ($i <= 0.25) return ProductConstant::COUHE;
        elseif ($i >= 0.30 && $i <= 0.50) return ProductConstant::ORDINARY;
        elseif ($i >= 0.55 && $i <= 0.75) return ProductConstant::GOOD;
        elseif ($i >= 0.80 && $i <= 1.00) return ProductConstant::BETTER;
        elseif ($i >= 1.05 && $i <= 1.25) return ProductConstant::BEST;
        elseif ($i >= 1.30) return ProductConstant::EXCELLENT;
        else return '';
    }

    /**
     * 分类专题——产品列表
     * @param $data
     * @param $productIdArr
     * @param $keyed
     * @return array|mixed
     */
    public static function getSpecialProducts($data, $productIdArr, $keyed)
    {
        $pageSize = isset($data['pageSize']) ? $data['pageSize'] : 1;
        $pageNum = isset($data['pageNum']) ? $data['pageNum'] : 5;
        $datas = [];

        foreach ($productIdArr as $pkey => $pval) {
            foreach ($keyed as $key => $val) {
                if ($key == $pval) {
                    $datas[] = $val;
                }
            }
        }

        $product = DateUtils::pageInfo($datas, $pageSize, $pageNum);
        return $product ? $product : [];
    }

    /**
     * 第二版本 分类专题——产品列表
     * @param array $params
     * @return array|mixed
     */
    public static function getProductSpecials($params = [])
    {
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 1;
        $pageNum = isset($params['pageNum']) ? $params['pageNum'] : 10;
        $productIds = $params['productIds'];
        $specialLists = $params['specialLists'];
        $datas = [];

        foreach ($productIds as $pkey => $pval) {
            foreach ($specialLists as $key => $val) {
                if ($key == $pval) {
                    $datas[] = $val;
                }
            }
        }

        $product = DateUtils::pageInfo($datas, $pageSize, $pageNum);
        return $product ? $product : [];
    }


    //////////////////////////////////////////////////////////////////////////////////////////
    //计算器

    public static function intRateStr($data)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '参考月利率';
        elseif ($i == 2) return '参考日利率';
        elseif ($i == 3) return '参考年利率';
        elseif ($i == 4) return '手续费';
        else return '';

    }

    /**
     * @param $data
     * @return string
     * @desc    显示“参考月利率”或者“参考日利率”从后台获取状态
     * 利率百分比从后台获取，获取不到时按1%月利率计算，并弹POP提示：获取数据失败，请重新加载
     * 计算器
     */
    public static function rateValue($data)
    {
        $value = floatval($data) . '';
        if (empty($data)) return '1';
        else return $value;
    }

    /**
     * @param $data
     * @return string
     * 除了一次性还款 其余的都为每期利息
     * 一次性还款 1 每期利息 2
     * 计算器
     */
    public static function interestPay($data)
    {
        $i = intval($data);
        if ($i == 3) return 1;
        else return 2;
    }

    /**
     * @param $data
     * 本金=在“主要变量”上那个金额处的选值，若金额处没有值，每期利息便显示“未选金额”
     * 期限=在“主要变量”上那个期限的选值，若期限处没有值，一次性还款便显示“未选期限”
     * @param $data
     * 每期利息： 【除了一次性还款其余的都为每期利息】
     * 金额=本金 x 每月利率/每日利率
     * 一次性还款：   9
     * 金额=本金 x每月利率/日利率 x 存款期限
     * 计算器
     */
    public static function calculateRate($product, $data)
    {
        $payMethod = intval($product['pay_method']);
        $balance = isset($data['balance']) ? intval($data['balance']) : '';
        $balanceTime = isset($data['balanceTime']) ? intval($data['balanceTime']) : '';
        //每月利率
        $yueRate = isset($data['min_rate']) ? $data['min_rate'] : 1;
        if (empty($balance)) {
            return $calculatedValue = '未选金额';
        } else if ($payMethod == 3 && empty($balanceTime)) {
            return $calculatedValue = '未选期限';
        } else if ($payMethod == 3 && !empty($balanceTime)) {
            //一次性还款
            return $calculatedValue = bcmul(bcmul($balance, $yueRate), $balanceTime);
        } else {
            //每期还款
            return $calculatedValue = bcmul($balance, $yueRate);
        }

    }

    /**
     * @param $data
     * @return string
     * @desc    欢迎程度转化
     * api4
     */
    public static function successCompare($data)
    {
        $i = DateUtils::toInt($data);
        if ($i <= 2000) return ProductConstant::COUHE;
        elseif ($i >= 2001 && $i <= 5000) return ProductConstant::ORDINARY;
        elseif ($i >= 5001 && $i <= 10000) return ProductConstant::GOOD;
        elseif ($i >= 10001 && $i <= 30000) return ProductConstant::BETTER;
        elseif ($i >= 30001 && $i <= 50000) return ProductConstant::BEST;
        elseif ($i >= 50001) return ProductConstant::EXCELLENT;
        else return '';
    }

    /**
     * @param $data
     * @return string
     * 欢迎程度转化
     */
    public static function formatSuccessCompare($data)
    {
        $i = DateUtils::toInt($data);
        if ($i <= 20000) return ProductConstant::COUHE;
        elseif ($i >= 20001 && $i <= 50000) return ProductConstant::ORDINARY;
        elseif ($i >= 50001 && $i <= 100000) return ProductConstant::GOOD;
        elseif ($i >= 100001 && $i <= 200000) return ProductConstant::BETTER;
        elseif ($i >= 200001 && $i <= 300000) return ProductConstant::BEST;
        elseif ($i >= 300001) return ProductConstant::EXCELLENT;
        else return '';
    }

    /**
     * @param $data
     * @return string
     * @desc    速度评价转化 根据放款速度满意度评价
     * api4
     */
    public static function fastCompare($data)
    {
        $i = floatval($data);
        if ($i <= 3) return ProductConstant::COUHE;
        elseif ($i >= 3.1 && $i <= 3.4) return ProductConstant::ORDINARY;
        elseif ($i >= 3.5 && $i <= 3.9) return ProductConstant::GOOD;
        elseif ($i >= 4.0 && $i <= 4.4) return ProductConstant::BETTER;
        elseif ($i >= 4.5 && $i <= 4.9) return ProductConstant::BEST;
        elseif ($i == 5.0) return ProductConstant::EXCELLENT;
        else return '';
    }

    /**
     * @param $data
     * @return int|string
     * @desc    通过概率转化图片的宽度
     * api4
     */
    public static function imgWidth($data)
    {
        $i = trim($data);
        if ($i == ProductConstant::COUHE) return 30;
        elseif ($i == ProductConstant::ORDINARY) return 60;
        elseif ($i == ProductConstant::GOOD) return 90;
        elseif ($i == ProductConstant::BETTER) return 120;
        elseif ($i == ProductConstant::BEST) return 150;
        elseif ($i == ProductConstant::EXCELLENT) return 208;
        else return '';
    }

    /**
     * 通过概率转化圆圈百分比
     * @param $data
     * @return int|string
     */
    public static function imgWidthToPercent($data)
    {
        $i = trim($data);
        if ($i == ProductConstant::COUHE) return '16.67';
        elseif ($i == ProductConstant::ORDINARY) return '33.33';
        elseif ($i == ProductConstant::GOOD) return '50';
        elseif ($i == ProductConstant::BETTER) return '66.67';
        elseif ($i == ProductConstant::BEST) return '83.33';
        elseif ($i == ProductConstant::EXCELLENT) return '100';
        else return '';
    }

    /**
     * 放款速度百分比
     * @param string $data
     * @return string
     */
    public static function imgWidthToFastPercent($data = '')
    {
        $i = trim($data);
        if ($i == ProductConstant::FAST_SPEED_IMPROVISE) return '16.67';
        elseif ($i == ProductConstant::FAST_SPEED_ORDINARY) return '33.33';
        elseif ($i == ProductConstant::FAST_SPEED_GOOD) return '50';
        elseif ($i == ProductConstant::FAST_SPEED_BETTER) return '66.67';
        elseif ($i == ProductConstant::FAST_SPEED_BEST) return '83.33';
        elseif ($i == ProductConstant::FAST_SPEED_EXCELLENT) return '100';
        else return '';
    }

    /**
     * @param $loanSpeed
     * @return string
     * 放款速度　转化为小时
     */
    public static function formatFast($loanSpeed)
    {
        //转化为小时
        $i = bcdiv($loanSpeed, 3600, 1);
        if ($i <= 3) return ProductConstant::EXCELLENT;
        elseif ($i >= 3.1 && $i <= 5) return ProductConstant::BEST;
        elseif ($i >= 5.1 && $i <= 8) return ProductConstant::BETTER;
        elseif ($i >= 8.1 && $i <= 12) return ProductConstant::GOOD;
        elseif ($i >= 12.1 && $i <= 16) return ProductConstant::ORDINARY;
        elseif ($i >= 16.1 && $i <= 24) return ProductConstant::COUHE;
        else return '';
    }

    /**
     * 放款速率范围
     * @param string $loanSpeed
     * @return string
     */
    public static function formatFastNum($loanSpeed = '')
    {
        //转化为小时
        $i = bcdiv($loanSpeed, 3600, 1);
        if ($i < 3) return ProductConstant::FAST_SPEED_EXCELLENT;
        elseif ($i >= 3 && $i < 5) return ProductConstant::FAST_SPEED_BEST;
        elseif ($i >= 5 && $i < 8) return ProductConstant::FAST_SPEED_BETTER;
        elseif ($i >= 8 && $i < 12) return ProductConstant::FAST_SPEED_GOOD;
        elseif ($i >= 12 && $i < 16) return ProductConstant::FAST_SPEED_ORDINARY;
        elseif ($i >= 16 && $i < 48) return ProductConstant::FAST_SPEED_IMPROVISE;
        else return '';
    }

    /**
     * @param $loanSpeed
     * @return string
     * 将时间秒转化为小时
     */
    public static function formatLoanSpeed($loanSpeed)
    {
        //转化为小时
        $loanSpeed = bcdiv($loanSpeed, 3600, 1);

        return $loanSpeed ? $loanSpeed : '';
    }

    /**
     * @param $loanSpeed
     * @return string
     * 将范围进行反转表示
     */
    public static function formatLoanSpeedToStar($loanSpeed)
    {
        //转化为小时
        $loanSpeed = bcdiv(bcdiv($loanSpeed, 3600), 4.8, 1);
        $loanSpeed = bcsub(5, $loanSpeed, 1);
        return $loanSpeed ? $loanSpeed : '';
    }


    /**
     * @param $data
     * @return mixed
     * @desc    产品详情
     * api4
     */
    public static function loanDetail($data)
    {
        $loan = [];
        //借款用途
        $loan[0]['title'] = isset($data['to_use']) ? '贷款类型' : '';
        $loan[0]['content'] = self::toUse($data['to_use']);
        //面向人群
        $loan[1]['title'] = !empty($data['user_group']) ? '面向人群' : '';
        $loan[1]['content'] = self::userGroup($data['user_group']);
        //审核方式
        $loan[2]['title'] = !empty($data['check_way']) ? '审核方式' : '';
        $loan[2]['content'] = self::checkWayToStr($data['check_way']);
        //到账方式
        $loan[3]['title'] = !empty($data['accept_method']) ? '到账方式' : '';
        $loan[3]['content'] = self::acceptMethod($data['accept_method']);
        //服务费
        $loan[4]['title'] = !empty($data['fee']) ? '服务费' : '';
        $loan[4]['content'] = isset($data['fee']) ? $data['fee'] : '';
        //到账金额 real_accept_money
        $loan[5]['title'] = isset($data['real_accept_money']) ? '实际到账' : '';
        $loan[5]['content'] = self::realAcceptMoneyToStr($data['real_accept_money'], $data['real_other']);
        //还款途径
        $loan[6]['title'] = !empty($data['pay_channel']) ? '还款途径' : '';
        $loan[6]['content'] = self::payChannel($data['pay_channel']);
        //还款方式
        $loan[7]['title'] = !empty($data['pay_method']) ? '还款方式' : '';
        $loan[7]['content'] = self::payMethodToStr($data['pay_method'], $data['pay_method_other']);
        //提前还款
        $loan[8]['title'] = !empty($data['is_ahead_pay']) ? '提前还款' : '';
        $loan[8]['content'] = self::isAheadPayToStr($data['is_ahead_pay'], $data['is_ahead_pay_other']);
        //逾期算法
        $loan[9]['title'] = !empty($data['overdue_alg']) ? '逾期算法' : '';
        $loan[9]['content'] = $data['overdue_alg'];
        //是否查征信
        $loan[10]['title'] = isset($data['credit_investigation']) ? '要查征信' : '';
        $loan[10]['content'] = self::creditInvestigationToStr($data['credit_investigation']);
        //能否提额 1能 0否
        $loan[11]['title'] = isset($data['raise_quota']) ? '能否提额' : '';
        $loan[11]['content'] = self::raiseQuotaToStr($data['raise_quota']);
        //所属平台
        $loan[12]['title'] = !empty($data['platform_name']) ? '所属平台' : '';
        $loan[12]['content'] = isset($data['platform_name']) ? $data['platform_name'] : '';
        //
        //过滤空数组
        foreach ($loan as $key => $val) {
            if ($val['title'] == '') {
                unset($loan[$key]);
            }
        }
        $loanData = array_values($loan);
        return $loanData;
    }


    /**
     * @param $payChannel
     * @return string
     * 还款途径 1等额本息,2等额本金, 3为还款日当天银行卡自动划扣 4为APP还款
     */
    public static function payChannel($payChannel)
    {
        $payChannels = explode(',', $payChannel);
        $payChannels = array_filter($payChannels);
        foreach ($payChannels as $key => $val) {
            $payChannels[$key] = self::payChannelToStr($val);
        }
        return implode($payChannels, "\\");
    }

    /**
     * @param $data
     * @return string
     * 还款途径 3为还款日当天银行卡自动划扣 4为APP还款
     */
    public static function payChannelToStr($data)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '等额本息';
        elseif ($i == 3) return '银行卡自动划扣';
        elseif ($i == 4) return 'APP主动还款';
        elseif ($i == 5) return '支付宝还款';
        elseif ($i == 6) return '微信还款';
        else return '';
    }

    /**
     * @param $data
     * @return string
     * 能否提额 1能 0否
     */
    public static function raiseQuotaToStr($data)
    {
        $i = DateUtils::toInt($data);
        if ($i == 0) return '否';
        elseif ($i == 1) return '能';
        else return '否';
    }

    /**
     * @param $data
     * @return string
     * 是否查征信 1是 0否
     */
    public static function creditInvestigationToStr($data)
    {
        $i = DateUtils::toInt($data);
        if ($i == 0) return '否';
        elseif ($i == 1) return '是';
        else return '否';
    }

    //to_use 处理 api4 借款用途；2为借现金，1为还信用卡,3为一次性还款,4为现金分期
    public static function toUse($toUse)
    {
        $i = DateUtils::toInt($toUse);
        if ($i == 1) return '还信用卡';
        elseif ($i == 2) return '借现金';
        elseif ($i == 3) return '一次性还款';
        elseif ($i == 4) return '现金分期';
        else return '';
    }

    //user_group 处理 api4
    public static function userGroup($userGroup)
    {
        $groupArr = explode(',', $userGroup);
        $groupArr = array_filter($groupArr);
        foreach ($groupArr as $key => $val) {
            $groupArr[$key] = self::userGroupToStr($val);
        }
        return implode($groupArr, "\\");
    }

    //user_group 转化成汉字      api4  面向人群；1为学生党，3生意人,2为上班族,4为自由职业；
    public static function userGroupToStr($data)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '学生党';
        elseif ($i == 2) return '上班族';
        elseif ($i == 3) return '生意人';
        elseif ($i == 4) return '自由职业';
        else return '';
    }

    //check_way审核方式转化成汉字    api4
    //审核方式；1为线下审批，2为部分电话复审，3为全自动审批 4为全部电话审批
    public static function checkWayToStr($data)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '线下审批';
        elseif ($i == 2) return '部分电话复审';
        elseif ($i == 3) return '全自动审批';
        elseif ($i == 4) return '全部电话审批';
        else return '';
    }

    //accept_method 到账方式转化成汉字   api4
    public static function acceptMethod($data)
    {
        $accessMethod = explode(',', $data);
        foreach ($accessMethod as $key => $val) {
            $accessMethod[$key] = self::acceptMethodToStr($val);
        }
        return implode($accessMethod, '、');
    }

    //accept_method 到账方式转化成汉字   api4
    public static function acceptMethodToStr($data)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '银行卡到账';
        elseif ($i == 2) return '信用卡到账';
        elseif ($i == 3) return '支付宝到账';
        else return '';
    }

    //pay_method 还款方式转化成汉字  api4  还款方式 3为一次性还款 4为分期还款
    public static function payMethodToStr($data, $other)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '等额本息';
        elseif ($i == 2) return '等额本金';
        elseif ($i == 3) return '一次性还款';
        elseif ($i == 4) return '分期还款';
        elseif ($i == 9) return $other;
        else return '';
    }

    //is_ahead_pay 提前还款转化成汉字    api4
    // 提前还款 1为不可以 2为可以
    public static function isAheadPayToStr($data, $other)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return '不可以';
        elseif ($i == 2) return '可以, 即时算息';
        elseif ($i == 3) return '可以, 利息全收';
        elseif ($i == 4) return '可以, 收违约金';
        elseif ($i == 5) return empty($other) ? '可以' : '可以，' . $other;
        elseif ($i == 9) return $other;
        else return '';
    }

    //is_ahead_pay 到账金额转化成汉字    api4
    //实际到账 1为全额到帐 0非全额到账
    public static function realAcceptMoneyToStr($data, $other)
    {
        $i = DateUtils::toInt($data);
        if ($i == 0) return empty($other) ? '非全额到账' : '非全额到账，' . $other;
        elseif ($i == 1) return '全额到帐';
        elseif ($i == 2) return '扣除服务费';
        elseif ($i == 3) return '扣除服务费和利息';
        elseif ($i == 4) return '可以, 收违约金';
        elseif ($i == 9) return $other;
        else return '';
    }

    /**
     * @param $datas
     * 首页推荐产品数据处理
     */
    public static function getRecommends($recommends)
    {
        foreach ($recommends as $key => $val) {
            $recommends[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
            $recommends[$key]['loan_min'] = DateUtils::formatMoneyToStr($val['loan_min']);
            $recommends[$key]['loan_max'] = DateUtils::formatMoneyToStr($val['loan_max']);
            $recommends[$key]['success_rate'] = DateUtils::formatPercentage($val['success_rate']);
        }

        return $recommends ? $recommends : [];
    }

    /**
     * @param $products
     * @param $times
     * @return mixed
     * 计算器
     */
    public static function getCounter($products, $times)
    {
        //利率
        $lilv = self::intRateStr($products['interest_alg']);
        $products['interest_alg_num'] = $products['interest_alg'];
        $products['interest_alg'] = $lilv;
        $products['interest_rate'] = self::rateValue($products['min_rate']);
        $products['avg_quota'] = empty($products['avg_quota']) ? '' : bcmul($products['avg_quota'], $times);
        //每期利息 || 一次性还款
        $products['pay_method'] = self::interestPay($products['pay_method']);
        if ($products['pay_method'] == 1) {
            $products['method'] = ProductConstant::REPAYMENT;
        } else {
            $products['method'] = ProductConstant::EACH_INTEREST;
        }
        //计算的最终结果
        //$datas['calculatedValue']   = Product::calculateRate($products,$data);
        //金额

        return $products;
    }

    /**
     * @return array
     * 1 2 6 8 4 7
     */
    public static function fetchProductTypes()
    {
        $types = [
            'experience' => [
                'id' => 1,
                'name' => '默认综合排名',
            ],
            'successRate' => [
                'id' => 2,
                'name' => '成功率',
            ],
            'online' => [
                'id' => 6,
                'name' => '新上线产品',
            ],
            'loanSpeed' => [
                'id' => 8,
                'name' => '速度',
            ],
            'compositeRate' => [
                'id' => 4,
                'name' => '利率',
            ],
            'loanMoney' => [
                'id' => 7,
                'name' => '额度',
            ],
        ];

        return $types ? $types : [];
    }

    /**
     * @param $type
     * @param $product
     * @param $countPage
     * @return array
     * 速贷大全列表 or 速贷大全搜索列表数据处理
     */
    public static function getProductsOrSearchs($type, $product, $countPage)
    {
        $dataAll = [];
        switch ($type) {
            case 1:     //默认综合排名
                foreach ($product as $key => $val) {
                    $product[$key]['position_sort'] = $val['position_sort'];
                    $product[$key]['star'] = $val['satisfaction'] . '';
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 2:     //成功率
                foreach ($product as $key => $val) {
                    $product[$key]['star'] = DateUtils::formatPercentage($val['success_rate']);
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                    $product[$key]['productType'] = intval($type);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 3:     //新上线产品
                foreach ($product as $key => $val) {
                    $product[$key]['star'] = DateUtils::formatDate($val['online_at']);
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                    $product[$key]['productType'] = intval($type);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 4:     //新放款速度
                foreach ($product as $key => $val) {
                    $product[$key]['star'] = $val['satisfaction'] . '';
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 5:     //贷款利率
                foreach ($product as $key => $val) {
                    $product[$key]['star'] = $val['min_rate'] . '%';
                    $product[$key]['rate_des'] = self::getProductRate($val['interest_alg']);
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
            case 6:     //平均额度
                foreach ($product as $key => $val) {
                    $product[$key]['star'] = bcadd($val['avg_quota'], 0);
                    $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
                    $product[$key]['tag_name'] = !empty($val['tag_name']) ? $val['tag_name'] : array();
                    $successCount = bcadd($val['success_count'], 0);
                    $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
                    $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
                    $product[$key]['productType'] = intval($type);
                    $product[$key]['fast_time'] = self::fetchFastTime($val['value']);
                }
                $dataAll['list'] = $product;
                $dataAll['pageCount'] = $countPage;
                break;
        }

        return $dataAll;
    }

    /**
     * @param $interestalg
     * @param $rate
     * @return string
     * 利率值转换  日利率：1%
     */
    public static function getProductRate($interestalg)
    {
        //1月息 2日息
        if ($interestalg == 1) {
            $str = '月利率';
        } elseif ($interestalg == 2) {
            $str = '日利率';
        } else {
            $str = '日利率';
        }

        return $str ? $str : '';
    }

    /**
     * @param $tagIdsAndPositions
     * @return array
     * 产品列表标签排序
     */
    public static function fetchTagsIdsByPostion($tagIdsAndPositions)
    {
        //排序后标签数组
        $tagDatas = [];
        //排序后产品id与标签数组
        $datas = [];
        //拼接标签存储数组
        $tagIdsDatas = [];
        //最后排序好的标签id数组
        $tagIds = [];
        //循环处理标签的排序
        foreach ($tagIdsAndPositions as $key => $val) {
            $positions = explode(',', $val['position']);
            $tags = explode(',', $val['tag_id']);
            foreach ($tags as $tkey => $tval) {
                $datas[$key][$tkey]['position'] = isset($positions[$tkey]) ? $positions[$tkey] : 0;
                $datas[$key][$tkey]['product_id'] = $val['platform_product_id'];
                $datas[$key][$tkey]['tag_id'] = $tval;
            }
        }
        //print_r($datas);die();
        //将标签id放入到tag_id为键值的数组中
        foreach ($datas as $dkey => $dval) {
            sort($dval);
            foreach ($dval as $k => $v) {
                $tagIdsDatas[$dkey]['product_id'] = $v['product_id'];
                $tagIdsDatas[$dkey]['tag_id'][$k] = $v['tag_id'];
            }
        }
        //print_r($tagIdsDatas);die();
        //取出tag_id放入到数组中，并用','号拼接
        foreach ($tagIdsDatas as $tagKey => $tagVal) {
            $tagIds[$tagKey]['platform_product_id'] = $tagVal['product_id'];
            $tagIds[$tagKey]['tag_id'] = implode(',', $tagVal['tag_id']);
        }
        //dd($tagIds);
        return $tagIds ? $tagIds : [];
    }

    /**
     * @param $product
     * @return array
     * 代还信用卡产品
     */
    public static function getGiveBackProducts($product)
    {
        foreach ($product as $key => $val) {
            $product[$key]['fast_time'] = self::formatFastTime($val['value']);
            $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
            $successCount = bcadd($val['success_count'], 0);
            $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
            $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
            //平均额度
            $product[$key]['avg_quota'] = DateUtils::formatNumToThousand($val['avg_quota']);
            //利率
            $product[$key]['min_rate'] = $val['min_rate'] . '%';
            $product[$key]['rate_des'] = self::getProductRate($val['interest_alg']);
        }

        return $product ? $product : [];
    }

    /**
     * @param array $historys
     * @return array
     * 产品申请记录产品数据转化
     */
    public static function getApplyHistory($historys = [])
    {
        $params = [];
        foreach ($historys as $key => $val) {
            if ($val['product']) {
                $params[$key]['id'] = $val['id'];
                $params[$key]['user_id'] = $val['user_id'];
                $params[$key]['platform_product_id'] = $val['platform_product_id'];
                $params[$key]['platform_id'] = $val['platform_id'];
                $params[$key]['is_urge'] = $val['is_urge'];
                $params[$key]['is_comment'] = $val['is_comment'];
                $params[$key]['created_at'] = DateUtils::formatDate($val['created_at']) . '申请';
                $params[$key]['created_time'] = DateUtils::formatDateToLeftdata($val['created_at']);
                $params[$key]['platform_product_name'] = $val['product']['platform_product_name'];
                $params[$key]['product_logo'] = QiniuService::getProductImgs($val['product']['product_logo'], $val['platform_product_id']);
                $loan_min = ProductStrategy::formatLoanMoney($val['product']['loan_min']);
                $loan_max = ProductStrategy::formatLoanMoney($val['product']['loan_max']);
                $period_min = ProductStrategy::formatPeriodTime($val['product']['period_min'], $val['product']['interest_alg']);
                $period_max = ProductStrategy::formatPeriodTime($val['product']['period_max'], $val['product']['interest_alg']);
                $params[$key]['loan_money'] = $loan_min . '-' . $loan_max . '元';
                if ($val['product']['interest_alg'] == 1) {
                    $params[$key]['period_time'] = $period_min . '-' . $period_max . '月';
                } else {
                    $params[$key]['period_time'] = $period_min . '-' . $period_max . '天';
                }
                $params[$key]['service_mobile'] = $val['product']['service_mobile'];
            }

        }

        return $params ? array_values($params) : [];
    }

    /**
     * @param $money
     * @return string
     * 金额大于10000  整除取整+万  不整除保留一位小数+万
     */
    public static function formatLoanMoney($money)
    {
        if ($money >= 10000 && ($money % 10000) == 0) {
            return sprintf("%.0f", $money / 10000) . '万';
        } elseif ($money >= 10000 && ($money % 10000) != 0) {
            return bcdiv($money, 10000, 1) . '万';
        } else {
            return $money . '';
        }
    }

    /**
     * @param $time
     * @param $type
     * @return float|string
     * 日利率 原数返回
     * 月利率 整除30 显示整数
     *       不整除30  四舍五入 保留一位小数
     */
    public static function formatPeriodTime($time, $type)
    {
        // 1为月息 2为日息
        if ($type == 1) {
            if ($time && ($time % 30) == 0) {
                return sprintf("%.0f", $time / 30);
            } else {
                return round($time / 30, 1);
            }
        } elseif ($type == 2) {
            return $time;
        } else {
            return $time;
        }
    }

    /**
     * 第三版 产品列表 Or 产品筛选数据处理
     * @param array $data
     * @return mixed
     */
    public static function getProductOrSearchLists($data = [])
    {
        //产品查看类型
        $type = isset($data['productType']) ? intval($data['productType']) : 1;
        $lists = $data['list'];
        $product = [];
        //推荐皇冠标识显示
        $vipProductIds = isset($data['vipProductIds']) ? $data['vipProductIds'] : [];
        //手机号
        $params['mobile'] = isset($data['mobile']) ? $data['mobile'] : '';

        foreach ($lists as $key => $val) {
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

            $product[$key]['productType'] = intval($type);
            $product[$key]['is_vip_product'] = isset($vipProductIds[$val['platform_product_id']]) ? 1 : 0;
            //$product[$key]['position_sort'] = isset($val['position_sort']) ? $val['position_sort'] : 9999;
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
     * 根据利息算法  将天转化为月
     * @param string $interest_alg
     * @param string $time
     * @return string
     */
    public static function formatDayToMonthByInterestalg($interest_alg = '', $time = '')
    {
        $interest_alg = intval($interest_alg);
        //利息算法 ，1为月息，2为日息，3为年息，4为手续费
        switch ($interest_alg) {
            case 1:
                //月息
                $res = sprintf("%.0f", $time / 30);
                break;
            case 2:
                //日息
                $res = $time;
                break;
            default:
                $res = '';
        }
        return $res;
    }

    /**
     * 第三版 产品详情
     * @param array $params
     * @return array
     */
    public static function getDetailPartOne($params = [])
    {
        //产品详情基础数据
        $info = $params['info'];
        $product = [];
        $product['platform_product_id'] = $info['platform_product_id'];
        $product['platform_id'] = $info['platform_id'];
        $product['platform_product_name'] = isset($info['platform_product_name']) ? $info['platform_product_name'] : '';
        $product['product_logo'] = QiniuService::getProductImgs($info['product_logo'], $info['platform_product_id']);
        $product['platform_name'] = isset($info['platform_name']) ? $info['platform_name'] : '';
        //额度
        if ($info['interval']) {
            //下拉菜单显示额度值
            $product['quota_sign'] = 1;
            $quota = range($info['loan_min'], $info['loan_max'], $info['interval']);
            foreach ($quota as $key => $val) {
                $quota[$key] = $val . '元';
            }
            $product['quota'] = $quota;
        } else {
            //可以手动输入
            $product['quota_sign'] = 0;
            $product['quota'] = [];
        }
        $loan_min = DateUtils::formatIntToThous($info['loan_min']);
        $loan_max = DateUtils::formatIntToThous($info['loan_max']);
        $product['loan_min'] = $info['loan_min'] . '元';
        $product['loan_max'] = $info['loan_max'] . '元';
        $product['loan_money'] = $loan_min . '~' . $loan_max . '元';
        //期限
        $period_min = ProductStrategy::formatDayToMonthByInterestalg($info['interest_alg'], $info['period_min']);
        $period_max = ProductStrategy::formatDayToMonthByInterestalg($info['interest_alg'], $info['period_max']);
        if ($info['interest_alg'] == 1) {
            //月息
            $product['period_max'] = $period_max . '个月';
            $product['loan_period'] = $period_min . '~' . $period_max . '个月';
        } else {
            //日息
            $product['period_max'] = $period_max . '天';
            $product['loan_period'] = $period_min . '~' . $period_max . '天';
        }
        $product['interest_alg'] = $info['interest_alg'];
        $product['term'] = ProductStrategy::getSecondEditionTermData($info);
        //申请人评价
        //申请人数
        $successCount = bcadd($info['success_count'], 0);
        $success_num = self::formatSuccessCompare($successCount);
        $product['success_width'] = self::imgWidthToPercent($success_num);
        $product['success_count'] = DateUtils::ceilMoney($successCount);
        //放款速度
        $fast_num = self::formatFast($params['loanSpeed']);
        $product['fast_width'] = self::imgWidthToPercent($fast_num);
        $product['fast_time'] = isset($info['fast_time']) ? $info['fast_time'] : 0;
        $product['fast_speed'] = self::formatLoanSpeed($params['loanSpeed']) . '小时';
        //平均额度
        $product['avg_quota'] = DateUtils::formatNumToThousand($info['avg_quota']);
        $quota_loan_max = empty($info['loan_max']) ? 1 : $info['loan_max'];
        $product['avg_quota_width'] = bcmul(bcdiv($info['avg_quota'], $quota_loan_max, 2), 100);
        //下款概率
        //$pass_num = self::formatPassCompare($info['success_rate']);
        $product['pass_width'] = bcmul($info['success_rate'], 20, 2);
        $product['pass_rate'] = DateUtils::formatPercentage($info['success_rate']);
        //审批条件
        $product['condition_tags'] = isset($params['condition_tags']) ? $params['condition_tags'] : [];
        //信用贴士
        $product['tips_tags'] = isset($params['tips_tags']) ? $params['tips_tags'] : [];
        //更多信息
        $product['loan_detail'] = self::getLoanDetails($info);
        //查看攻略
        $product['news_link'] = isset($info['news_link']) ? $info['news_link'] : '';
        //分享链接
        $product['h5_link'] = LinkUtils::thirdEditionProductShare($params['productId']);
        //申请借款开关判断
        $product['apply_button_text_flag'] = 'v2.5.1';
        $product['apply_button_text'] = '查看攻略';
        //加密手机号
        $product['mobile'] = ProductStrategy::fetchEncryptMobile($params);
        //对接标识
        $product['type_nid'] = $info['type_nid'] ? strtolower($info['type_nid']) : '';

        return $product;
    }

    /**
     * 第三版  产品详情更多信息
     * @param array $data
     * @return array
     */
    public static function getLoanDetails($data = [])
    {
        //审核方式
        $loan[0]['title'] = '审核方式';
        $loan[0]['content'] = self::checkWayToStr($data['check_way']);
        //到账方式
        $loan[1]['title'] = '到账方式';
        $loan[1]['content'] = self::acceptMethod($data['accept_method']);
        //还款途径
        $loan[2]['title'] = '还款途径';
        $loan[2]['content'] = self::payChannel($data['pay_channel']);
        //还款方式
        $loan[3]['title'] = '还款方式';
        $loan[3]['content'] = self::payMethodToStr($data['pay_method'], $data['pay_method_other']);

        return $loan ? $loan : [];
    }

    /**
     * 根据利息转化时间
     * @param array $params
     * @return array
     */
    public static function getLoanTimesByInterest($params = [])
    {
        $i = isset($params['interest_alg']) ? $params['interest_alg'] : 0;
        switch ($i) {
            case 1:
                //月息
                $loanTimes = bcdiv($params['loanTimes'], 30, 5);
                break;
            case 2:
                //日息
                $loanTimes = $params['loanTimes'];
                break;
            case 3:
                //年息
                $loanTimes = bcdiv($params['loanTimes'], 360, 5);
                break;
            case 4:
                //手续费
                $loanTimes = 0;
                break;
            default:
                $loanTimes = 0;
        }

        $params['loanTimes'] = $loanTimes;
        return $params;
    }

    /**
     * 根据利息转化时间
     * @param array $params
     * @return array
     */
    public static function getFormatLoanTimesByInterest($params = [])
    {
        $i = isset($params['interest_alg']) ? $params['interest_alg'] : 0;
        switch ($i) {
            case 1:
                //月息
                $loanTimes = bcdiv($params['loanTimes'], 30, 5);
                break;
            case 2:
                //日息
                $loanTimes = $params['loanTimes'];
                break;
            case 3:
                //年息
                $loanTimes = bcdiv($params['loanTimes'], 360, 5);
                break;
            case 4:
                //手续费
                $loanTimes = 0;
                break;
            default:
                $loanTimes = 0;
        }

        $params['format_loanTimes'] = $loanTimes;
        return $params;
    }

    /**
     * 第三版 产品详情计算器 将利率全部转化为利率费用
     * @operator  计算方式, 1加法方式, 2利率方式
     * @date_relate 日期相关, 1相关, 0不相关
     * @param array $params
     * @return array
     */
    public static function getCalculatorCost($params = [])
    {
        //dd($params);
        //金额
        $loanMoney = $params['loanMoney'];
        //期限
        $loanTimes = $params['loanTimes'];
        //费用
        $cost = $params['fee'];
        //计算结果
        foreach ($cost as $key => $value) {
            $calcu = 0;
            //加利率与日期不相关
            if ($value['operator'] == 2 && $value['date_relate'] == 0) {
                $calcu = $loanMoney * $value['value'];
            }
            //加利率与日期相关
            if ($value['operator'] == 2 && $value['date_relate'] == 1) {
                $calcu = $loanMoney * $value['value'] * $loanTimes;
            }
            //减利率与日期不先关
            if ($value['operator'] == 4 && $value['date_relate'] == 0) {
                $calcu = $loanMoney * $value['value'];
            }
            //减利率与日期相关
            if ($value['operator'] == 4 && $value['date_relate'] == 1) {
                $calcu = $loanMoney * $value['value'] * $loanTimes;
            }
            if (!empty($calcu)) {
                $cost[$key]['value'] = $calcu;
            }
        }

        return $cost ? $cost : [];
    }

    /**
     * 数据格式处理，加和求总计
     * @param array $params
     * @return array
     */
    public static function getCalculatorTotal($params = [])
    {
        //费用
        $cost = $params['cost'];
        //逾期费
        $overdueAlg = $params['overdue_alg'];
        $datas = [];
        //累加
        $add = 0;
        //累减
        $sub = 0;
        //还款金额
        $repay_money = 0;
        //到账金额
        $loanMoney = 0;
        //息费合计
        $addTotal = 0;
        //数据格式处理
        foreach ($cost as $key => $value) {
            $datas[$key]['name'] = $value['name'];
            $datas[$key]['value'] = DateUtils::formatData($value['value']) . '元';
            //减法
            if (3 == $value['operator'] || 4 == $value['operator']) {
                $sub = $sub + $value['value'];
            } else {
                $add = $add + $value['value'];
            }
            //息费合计
            $addTotal = bcadd($add, $sub, 2);
            //到账金额
            $loanMoney = bcsub($params['loanMoney'], $sub, 2);
            //还款金额
            $repay_money = bcadd($params['loanMoney'], $add, 2);
        }
        $res['cost'] = $datas;
        //息费合计
        $total[0]['name'] = empty($addTotal) ? '' : '息费合计';
        $total[0]['value'] = DateUtils::formatData($addTotal) . '元';
        //到账金额
        $total[1]['name'] = empty($loanMoney) ? '' : '到账金额';
        $total[1]['value'] = DateUtils::formatData($loanMoney) . '元';
        //还款金额
        $total[2]['name'] = empty($repay_money) ? '' : '还款金额';
        $total[2]['value'] = DateUtils::formatData($repay_money) . '元';
        //逾期费
        $total[3]['name'] = empty($overdueAlg) ? '' : '逾期费';
        $total[3]['value'] = $overdueAlg;
        //过滤空数组
        foreach ($total as $key => $val) {
            if ($val['name'] == '') {
                unset($total[$key]);
            }
        }
        $res['total'] = array_values($total);

        return $res ? $res : [];
    }

    /**
     * base64加密获取手机号
     * @param array $params
     * @return string
     */
    public static function fetchEncryptMobile($params = [])
    {
        $mobile = $params['mobile'];
        $salt = md5('sudaizhijia');
        $baseEncrypt = base64_encode($mobile);
        return substr_replace($baseEncrypt, strtoupper($salt), 7, 0);
    }

    /**
     * 第二版  首页产品推荐
     * @param array $params
     * @return array
     */
    public static function getSecondEditionRecommends($params = [])
    {

        //产品查看类型
        $type = isset($params['productType']) ? intval($params['productType']) : 1;
        $product = [];
        foreach ($params['list'] as $key => $val) {
            $product[$key]['platform_product_id'] = $val['platform_product_id'];
            $product[$key]['platform_id'] = $val['platform_id'];
            $product[$key]['platform_product_name'] = $val['platform_product_name'];
            $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
            //额度
            $loan_min = DateUtils::formatIntToThousBandunit($val['loan_min']);
            $loan_max = DateUtils::formatIntToThousBandunit($val['loan_max']);
            $product[$key]['quota'] = $loan_min . '~' . $loan_max;
            $product[$key]['productType'] = intval($type);
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
     * 账单代还产品数据处理
     * @param array $params
     * @return array
     */
    public static function getBillGiveBackProducts($params = [])
    {
        $product = [];
        foreach ($params as $key => $val) {
            $product[$key]['platform_product_id'] = $val['platform_product_id'];
            $product[$key]['platform_id'] = $val['platform_id'];
            $product[$key]['platform_product_name'] = $val['platform_product_name'];
            $product[$key]['product_logo'] = QiniuService::getProductImgs($val['product_logo'], $val['platform_product_id']);
            $product[$key]['product_introduct'] = ComModelFactory::escapeHtml($val['product_introduct']);
            $product[$key]['fast_time'] = self::formatFastTime($val['value']);
            $successCount = bcadd($val['success_count'], 0);
            $product[$key]['success_count'] = DateUtils::ceilMoney($successCount);
            //平均额度
            $product[$key]['avg_quota'] = $val['avg_quota'] > 10000 ? round(($val['avg_quota'] / 10000), 1) . '万' : $val['avg_quota'];
            //利率
            $product[$key]['min_rate'] = $val['min_rate'] . '%';
            $product[$key]['rate_des'] = self::getProductRate($val['interest_alg']);
        }

        return $product;
    }

    /**
     * 筛选标签数据处理
     * @param array $params
     * @return array
     */
    public static function getSeoTags($params = [])
    {
        $data = [];
        foreach ($params as $key => $val) {
            $data[$key]['id'] = $val['id'];
            $data[$key]['value'] = $val['name'];
        }

        return $data ? $data : [];
    }

    /**
     * 产品H5注册链接地址
     * @param array $info
     * @return array
     */
    public static function getProductUrl($info = [])
    {
        $data = [];
        $data['product_h5_url'] = isset($info['product_h5_url']) ? $info['product_h5_url'] : '';

        return $data;
    }

    /**
     * 速贷大全 - 综合推荐 - vip在下
     * @param array $params
     * @return array
     */
    public static function getProductListsSortByVip($params = [])
    {
        $positionSort = [];
        foreach ($params as $key => $val) {
            $volume[$key] = isset($val['is_vip_product']) ? $val['is_vip_product'] : 0;
            $positionSort[$key] = isset($val['position_sort']) ? $val['position_sort'] : 0;
        }

        if ($params) {
            array_multisort($volume, SORT_ASC, $positionSort, SORT_ASC, $params);
        }

        return $params ? $params : [];
    }

    /**
     * 第四版本产品详情第一部分 - 速贷大数据
     * @param array $params
     * @return array
     */
    public static function getDetailProductDatas($params = [])
    {
        //产品详情基础数据
        $info = $params['info'];
        $product = [];
        //----------------------计算器数据-----------------
        //额度
        if ($info['interval']) {
            //下拉菜单显示额度值
            $product['quota_sign'] = 1;
            $quota = range($info['loan_min'], $info['loan_max'], $info['interval']);
            foreach ($quota as $key => $val) {
                $quota[$key] = $val . '元';
            }
            $product['quota'] = $quota;
        } else {
            //可以手动输入
            $product['quota_sign'] = 0;
            $product['quota'] = [];
        }
        $loan_min = DateUtils::formatIntToThous($info['loan_min']);
        $loan_max = DateUtils::formatIntToThous($info['loan_max']);
        $product['loan_min'] = $info['loan_min'] . '元';
        $product['loan_max'] = $info['loan_max'] . '元';
        $product['loan_money'] = $loan_min . '~' . $loan_max . '元';
        //期限
        $period_min = ProductStrategy::formatDayToMonthByInterestalg($info['interest_alg'], $info['period_min']);
        $period_max = ProductStrategy::formatDayToMonthByInterestalg($info['interest_alg'], $info['period_max']);
        if ($info['interest_alg'] == 1) {
            //月息
            $product['period_max'] = $period_max . '个月';
            $product['loan_period'] = $period_min . '~' . $period_max . '个月';
        } else {
            //日息
            $product['period_max'] = $period_max . '天';
            $product['loan_period'] = $period_min . '~' . $period_max . '天';
        }
        $product['interest_alg'] = $info['interest_alg'];
        $product['term'] = ProductStrategy::getSecondEditionTermData($info);
//        //默认到账金额，总还款金额，利息和服务费描述
//        $product['total_desc'] = ProductConstant::TOTAL_DESC;
//        $product['account_desc'] = ProductConstant::ACCOUNT_DESC;
//        $product['interest_desc'] = ProductConstant::INTEREST_DESC;
//        $product['repay_desc'] = ProductConstant::REPAY_DESC;
        //日利率描述
        $product['interest_rate_desc'] = ProductStrategy::getMinRateDesc($info) . ':' . $info['min_rate'] . '%';;
        //优选理由
        $product['is_preference'] = $info['is_preference'];
        $product['product_introduct'] = $info['product_introduct'];

        //-------------------产品大数据-----------------------------------
        $product['platform_product_id'] = $info['platform_product_id'];
        $product['platform_id'] = $info['platform_id'];
        $product['platform_product_name'] = isset($info['platform_product_name']) ? $info['platform_product_name'] : '';
        $product['product_logo'] = QiniuService::getProductImgs($info['product_logo'], $info['platform_product_id']);
        $product['platform_name'] = isset($info['platform_name']) ? $info['platform_name'] : '';

        //申请人评价
        //申请人数
        $successCount = bcadd($info['success_count'], 0);
        $success_num = self::formatSuccessCompare($successCount);
        $product['success_width'] = self::imgWidthToPercent($success_num);
        $product['success_count'] = DateUtils::ceilMoney($successCount);
        $todayTotalCount = bcadd($info['total_today_count'], 0);
        //今日申请总数
        $product['total_today_count'] = DateUtils::ceilMoney($todayTotalCount);
        //放款速度
        $fast_num = self::formatFastNum($params['loanSpeed']);
        $product['fast_width'] = self::imgWidthToFastPercent($fast_num);
        $product['fast_time'] = isset($info['fast_time']) ? $info['fast_time'] : 0;
        $product['fast_speed'] = self::formatLoanSpeed($params['loanSpeed']) . '小时';
        $product['fast_speed_desc'] = $fast_num;

        //平均额度
        $product['avg_quota'] = DateUtils::formatNumToThousand($info['avg_quota']);
        $quota_loan_max = empty($info['loan_max']) ? 1 : $info['loan_max'];
        $product['avg_quota_width'] = bcmul(bcdiv($info['avg_quota'], $quota_loan_max, 2), 100);
        //下款概率
        //$pass_num = self::formatPassCompare($info['success_rate']);
        $product['pass_width'] = bcmul($info['success_rate'], 20, 2);
        $product['pass_rate'] = DateUtils::formatPercentage($info['success_rate']);
        //审批条件
        $product['condition_tags'] = isset($params['condition_tags']) ? $params['condition_tags'] : [];
        //信用贴士
        $product['tips_tags'] = isset($params['tips_tags']) ? $params['tips_tags'] : [];
        //更多信息
        $product['loan_detail'] = self::getLoanDetails($info);
        //查看攻略
        $product['news_link'] = isset($info['news_link']) ? $info['news_link'] : '';
        //分享链接
        $product['h5_link'] = LinkUtils::thirdEditionProductShare($params['productId']);
        //申请借款开关判断
        $product['apply_button_text_flag'] = 'v2.5.1';
        $product['apply_button_text'] = '查看攻略';
        //加密手机号
        $product['mobile'] = ProductStrategy::fetchEncryptMobile($params);
        //对接标识
        $product['type_nid'] = $info['type_nid'] ? strtolower($info['type_nid']) : '';

        return $product;
    }

    /**
     * 第四版计算器 - 费率计算
     * @param array $params
     * @return array|mixed
     */
    public static function getCalculatorInterestInfo($params = [])
    {
        //dd($params);
        //金额
        $loanMoney = $params['loanMoney'];
        //转化后期限 【单位：月/日】
        $formatLoanTimes = $params['format_loanTimes'];
        //费用
        $cost = $params['fee'];
        //逾期费
        $overdueAlg = $params['overdue_alg'];
        //计算结果
        foreach ($cost as $key => $value) {
            $calcu = 0;
            //加利率与日期不相关
            if ($value['operator'] == 2 && $value['date_relate'] == 0) {
                $calcu = $loanMoney * $value['value'];
            }
            //加利率与日期相关
            if ($value['operator'] == 2 && $value['date_relate'] == 1) {
                $calcu = $loanMoney * $value['value'] * $formatLoanTimes;
            }
            //减利率与日期不先关
            if ($value['operator'] == 4 && $value['date_relate'] == 0) {
                $calcu = $loanMoney * $value['value'];
            }
            //减利率与日期相关
            if ($value['operator'] == 4 && $value['date_relate'] == 1) {
                $calcu = $loanMoney * $value['value'] * $formatLoanTimes;
            }
            if (!empty($calcu)) {
                $cost[$key]['value'] = $calcu;
            }
        }

        //【贷款周期】后一次性还款：贷款金额数字
        $datas = [];
        if ($params['pay_method'] == 3)//一次性还款月
        {
            if ($params['interest_alg'] == 1) { //月
                $datas['format_loan_times'] = intval($formatLoanTimes) . '月';
            } elseif ($params['interest_alg'] == 2) { //日
                $datas['format_loan_times'] = intval($formatLoanTimes) . '天';
            }

        } elseif ($params['pay_method'] == 4) //分期还款
        {
            //月还款金额：【利息算法】设置为【月息】，借款金额*月贷款利率+借款金额/天数
            $minRate = bcdiv($params['min_rate'], 100, 5);
            $pay_money = bcmul($minRate, $params['loanMoney']) + bcdiv($params['loanMoney'], $formatLoanTimes);
            //SLogger::getStream()->info('计算器',['data'=>$params,'rate'=>$minRate]);
            $datas['pay_money'] = round($pay_money, 2);
        }

        $res['payInfo'] = $datas ? $datas : [];
        $res['cost'] = $cost ? $cost : [];
        return $res ? $res : [];
    }

    /**
     * 第四版计算器 - 计算器最终计算结果
     * @param array $params
     * @return array
     */
    public static function getCalculatorTotalRes($params = [])
    {
        //一次性还款信息
        $payInfo = $params['cost']['payInfo'];
        //费用
        $cost = $params['cost']['cost'];
        //逾期费
        $overdueAlg = $params['overdue_alg'];
        $datas = [];
        //累加
        $add = 0;
        //累减
        $sub = 0;
        //还款金额
        $repay_money = 0;
        //到账金额
        $loanMoney = 0;
        //息费合计
        $addTotal = 0;
        //数据格式处理
        foreach ($cost as $key => $value) {
            $datas[$key]['name'] = $value['name'];
            $datas[$key]['value'] = DateUtils::formatData($value['value']);
            //减法
            if (3 == $value['operator'] || 4 == $value['operator']) {
                $sub = $sub + $value['value'];
            } else {
                $add = $add + $value['value'];
            }
            //息费合计
            $addTotal = bcadd($add, $sub, 2);
            //到账金额
            $loanMoney = bcsub($params['loanMoney'], $sub, 2);
            //还款金额
            $repay_money = bcadd($params['loanMoney'], $add, 2);
        }

        $res['cost'] = $datas;
        //总还款金额
        $total[0]['name'] = empty($repay_money) ? '' : '总还款金额(元)';
        $total[0]['value'] = DateUtils::formatData($repay_money) . '';
        //到账金额
        $total[1]['name'] = empty($loanMoney) ? '' : '到账金额(元)';
        $total[1]['value'] = DateUtils::formatData($loanMoney) . '';
        //利息和服务费
        $total[2]['name'] = empty($addTotal) ? '' : '利息和服务费(元)';
        $total[2]['value'] = DateUtils::formatData($addTotal) . '';
        //日/月还款金额(元) pay_method 还款方式 3为一次性还款 4为分期还款
        //3月后一次性还款：先收利息：借款额度；
        if (3 == $params['pay_method'] && $repay_money < $params['loanMoney'])//先收利息：借款额度
        {
            $total[3]['name'] = empty($params['loanMoney']) ? '' : $payInfo['format_loan_times'] . '后一次性还款(元)';
            $total[3]['value'] = $params['loanMoney'] . '';
        } elseif (3 == $params['pay_method'] && $repay_money >= $params['loanMoney']) //后收利息：总还款金额
        {
            $total[3]['name'] = empty($repay_money) ? '' : $payInfo['format_loan_times'] . '后一次性还款(元)';
            $total[3]['value'] = DateUtils::formatData($repay_money) . '';
        } elseif ($params['pay_method'] == 4 && $params['interest_alg'] == 1) //月利率
        {
            //月还款金额：【利息算法】设置为【月息】，借款金额*月贷款利率+借款金额/天数
            $total[3]['name'] = empty($payInfo['pay_money']) ? '' : '月还款金额(元)';
            $total[3]['value'] = round($payInfo['pay_money']) . '';
        } elseif ($params['pay_method'] == 4 && $params['interest_alg'] == 2) //日利率
        {
            //日还款金额：【利息算法】设置为【日息】，借款金额*日贷款利率+借款金额/天数
            $total[3]['name'] = empty($payInfo['pay_money']) ? '' : '日还款金额(元)';
            $total[3]['value'] = round($payInfo['pay_money']) . '';
        }

        //过滤空数组
        foreach ($total as $key => $val) {
            if ($val['name'] == '') {
                unset($total[$key]);
            }
        }
        $total = array_filter($total);
        $res['total'] = array_values($total);

        return $res ? $res : [];
    }

    /**
     * 利息数据格式处理
     * @param array $params
     * @return mixed
     */
    public static function getCalculatorInterests($params = [])
    {
        //名词参数
        $interestInfo = $params['params'];
        $overdueAlg = $interestInfo['overdue_alg'];
        //利息 20，按月计费，月费率0.83%-2%
        $cost = $params['cost'];
        foreach ($cost as $key => $val) {
            if (isset($val['name']) && $val['name'] == '利息' && !empty($val['value'])) {
                $cost[$key]['value'] = empty($val['value']) ? '' : $val['value'] . '元，按' . $interestInfo['interest_alg_desc'] . '计费，' . $interestInfo['interest_alg_desc'] . '费率' . $interestInfo['min_rate'] . '%-' . $interestInfo['max_rate'] . '%';
            } else {
                $cost[$key]['name'] = empty($val['value']) ? '' : $val['name'];
                $cost[$key]['value'] = empty($val['value']) ? '' : $val['value'] . '元';
            }
        }
        //逾期费
        $overdueAlgData['name'] = empty($overdueAlg) ? '' : '逾期费';
        $overdueAlgData['value'] = $overdueAlg;
        array_push($cost, $overdueAlgData);
        //去除空数据项
        foreach ($cost as $item => $value) {
            $cost[$item] = array_filter($value);
        }
        $cost = array_filter($cost);
        $res['cost'] = array_values($cost);
        $res['total'] = $params['total'];

        return $res;
    }

    /**
     * 组合计算器需要数据
     * @param array $datas
     * @return array
     */
    public static function getCalculatorFormatData($datas = [])
    {
        $info = $datas['info'];
        //没有额度，默认最小额度
        $params['loanMoney'] = empty($datas['loanMoney']) ? $info['loan_max'] : $datas['loanMoney'];
        $params['loanTimes'] = empty($datas['loanTimes']) ? $info['period_max'] : $datas['loanTimes'];
        //利息算法 ，1为月息，2为日息，3为年息，4为手续费
        $params['interest_alg'] = $info['interest_alg'];
        $params['interest_alg_desc'] = ProductStrategy::getInterestAlgDesc($info['interest_alg']);
        //日月利率
        $params['min_rate'] = $info['min_rate'];
        $params['max_rate'] = $info['max_rate'];
        //还款方式 3为一次性还款 4为分期还款
        $params['pay_method'] = $info['pay_method'];
        //
        $params['overdue_alg'] = $info['overdue_alg'];

        return $params ? $params : [];
    }

    /**
     * 利息算法 ，1为月息，2为日息，3为年息，4为手续费
     * @param string $param
     * @return mixed|string
     */
    public static function getInterestAlgDesc($param = '')
    {
        $i = intval($param);
        $interests = [
            1 => '月',
            2 => '日',
            3 => '年',
            4 => '手续费',
        ];

        return $interests[$i] ? $interests[$i] : '';
    }

    /**
     * 利息算法 ，1为月息，2为日息，3为年息，4为手续费
     * @param array $params
     * @return string
     */
    public static function getMinRateDesc($params = [])
    {
        $i = intval($params['interest_alg']);
        switch ($i) {
            case 1: //月息
                $desc = '月利率';
                break;
            case 2: //日息
                $desc = '日利率';
                break;
            case 3: //年息
                $desc = '年利率';
                break;
            case 4: //手续费
                $desc = '手续费';
                break;
            default:
                $desc = '';
        }

        return $desc;
    }
}