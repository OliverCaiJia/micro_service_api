<?php

namespace App\Strategies;

use App\Constants\CreditConstant;
use App\Constants\UserVipConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\CreditFactory;
use App\Models\Factory\ProductFactory;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * 积分公共策略
 *
 * Class UserStrategy
 * @package App\Strategies
 */
class CreditStrategy extends AppStrategy
{
    /**
     * 积分号
     */
    public static function creditNid()
    {
        $nid = date('Y') . date('m') . date('d') . date('H') . date('i') . date('s') . UserStrategy::getRandChar(6);
        return 'credit_' . $nid;
    }

    /**
     * @param array $applyArr
     * 返回产品申请数据
     */
    public static function getProductApply($userId, $applyArr = [])
    {
        $productArr = [];
        //产品信息
        foreach ($applyArr as $key => $val) {
            $product = ProductFactory::getProductLogoAndName($val['product_id']);
            $logObj = CreditFactory::getCreditProductLog($userId, $val['id']);
            if (!empty($logObj)) {
                $productArr[$key]['apply_sign'] = CreditConstant::SIGN_FULL;
            } else {
                $productArr[$key]['apply_sign'] = CreditConstant::DEFAULT_EMPTY;
            }
            $productArr[$key]['config_id'] = $val['id'];
            $productArr[$key]['config_credits'] = $val['credits'];
            $productArr[$key]['product_id'] = $product['platform_product_id'];
            $productArr[$key]['product_name'] = $product['platform_product_name'];
            $productArr[$key]['product_logo'] = QiniuService::getProductImgs($product['product_logo'], $product['platform_product_id']);
        }
        return $productArr;
    }

    /** 处理列表数据
     * @param $data
     */
    public static function getCreditData($data, $offset = 0)
    {
        if (!empty($data)) {
            // 1.构建日期数组 让当前日期连续
            $max_time = strtotime($data['0']->date . ' 23:59:59');
            if (!$offset) {
                $max_time = time();
            }

            $min_time = strtotime($data[count($data) - 1]->date);

            $dates = [];
            $date = $max_time;
            $index = 0;
            while ($date >= $min_time) {
                $dates[] = date('Y-m', $date);
                $index++;
                $date = strtotime("-$index month", $max_time);
            }

            $dates = array_flip($dates);

            // 2.处理数据格式
            foreach ($data as $item) {
                if (key_exists($item->title_date, $dates)) {
                    unset($dates[$item->title_date]);
                }
            }

            // 插入未存在月份数据
            foreach ($dates as $date => $v) {
                $obj = new \StdClass();
                $obj->name = '';
                $obj->score = 0;
                $time = strtotime($date);
                $obj->date = date('Y-m-d', $time);
                $obj->title_date = $date;
                $obj->type_status = 0;
                $obj->type_nid = '';
                $obj->credit = 0;
                $obj->create_at = date('Y-m-d H:i:s', $time);
                $data[] = $obj;
            }

            // 偏移的情况下　去除第一条
            if ($offset) {
                unset($data[0]);
            }

            // 根据日期排序
            usort($data, function ($a, $b) {
                return strtotime($a->create_at) > strtotime($b->create_at) ? -1 : 1;
            });

            foreach ($data as &$item) {
                // 生成积分字符串
                $item->score = ($item->type_status == 1) ? '+' . $item->credit : '-' . $item->credit;
                $item->nid = strtotime($item->title_date);
                $item->current = static::isCurrentMonth($item->title_date);
                $item->month = date('Y年m月', strtotime($item->title_date));
            }

            unset($item);
            return $data;
        }

        return [];
    }

    public static function isCurrentMonth($date)
    {
        $now = strtotime(date('Y-m', time()));
        $time = strtotime($date);
        return $now == $time;
    }

    /**
     * 用户签到加积分类型
     * @param array $params
     * @return string
     */
    public static function getSignTypeNid($params = [])
    {
        //普通用户
        if (empty($params['type']) || empty($params['userVipType'])) {
            //1倍积分
            $typeNid = CreditConstant::ADD_INTEGRAL_USER_SIGN_TYPE;
        } elseif ($params['type'] == UserVipConstant::VIP_ADD_CREDIT && $params['userVipType']) {
            //新版 && 会员 1.5倍积分
            $typeNid = CreditConstant::ADD_INTEGRAL_DOUBLE_USER_SIGN_TYPE;
        } else {
            ////1倍积分
            $typeNid = CreditConstant::ADD_INTEGRAL_USER_SIGN_TYPE;
        }

        return $typeNid;
    }
}
