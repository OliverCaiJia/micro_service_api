<?php

namespace App\Strategies;

use App\Constants\UserBillPlatformConstant;
use App\Helpers\DateUtils;
use App\Helpers\Utils;
use App\Models\Factory\UserBillFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * 用户账单平台策略
 * Class UserBillStrategy
 * @package App\Strategies
 */
class UserBillPlatformStrategy extends AppStrategy
{
    /**
     * 信用卡平台银行列表
     * @param array $params
     * @return array
     */
    public static function getBillPlatformBanks($params = [])
    {
        $banks = [];
        foreach ($params as $key => $value) {
            $banks[$key]['id'] = $value['id'];
            $banks[$key]['bank_short_name'] = $value['bank_short_name'];
        }

        return $banks;
    }

    /**
     * 创建或修改完成信用卡平台之后返回数据信息
     * @param array $params
     * @return array
     */
    public static function getUpdateCreditcardInfo($params = [])
    {
        $bankinfo = $params['bankinfo'];
        $params['update']['bank_logo'] = isset($bankinfo['bank_logo']) ? QiniuService::getImgs($bankinfo['bank_logo']) : '';

        return $params['update'] ? $params['update'] : [];
    }

    /**
     * 信用卡账单平台数据处理
     * @param array $params
     * @return array
     */
    public static function getCreditcards($params = [])
    {
        $creditcards = [];
        foreach ($params as $key => $val) {
            $creditcards[$key]['id'] = $val['id'];
            $creditcards[$key]['bank_conf_id'] = $val['bank_conf_id'];
            $creditcards[$key]['bank_credit_card_num'] = $val['bank_credit_card_num'];
            $creditcards[$key]['bank_name_on_card'] = $val['bank_name_on_card'];
            $creditcards[$key]['bank_bill_date'] = UserBillPlatformStrategy::formatBillDate($val['bank_bill_time']);
            $creditcards[$key]['bank_repay_day'] = UserBillPlatformStrategy::formatBillDate($val['repay_time']);
            $creditcards[$key]['bank_quota'] = DateUtils::formatIntToThou($val['bank_quota']);
            $creditcards[$key]['bank_use_points'] = $val['bank_use_points'] . '';
            $creditcards[$key]['bank_min_amount'] = $val['bill_money'] == '--' ? '--' : $val['bank_min_amount'];
            $creditcards[$key]['repay_alert_status'] = $val['repay_alert_status'];
            $creditcards[$key]['is_hidden'] = $val['is_hidden'];
            $creditcards[$key]['bank_is_import'] = $val['bank_is_import'];
            $creditcards[$key]['bank_short_name'] = isset($val['bankInfo']['bank_short_name']) ? $val['bankInfo']['bank_short_name'] : '';
            $creditcards[$key]['bank_logo'] = isset($val['bankInfo']['bank_logo']) ? QiniuService::getImgs($val['bankInfo']['bank_logo']) : '';
            $creditcards[$key]['bank_watermark_link'] = isset($val['bankInfo']['bank_watermark_link']) ? QiniuService::getImgs($val['bankInfo']['bank_watermark_link']) : '';
            $creditcards[$key]['bank_bg_color'] = isset($val['bankInfo']['bank_bg_color']) ? UserBillPlatformStrategy::getBankBgColor($val['bankInfo']['bank_bg_color']) : '';
            $creditcards[$key]['button_sign'] = $val['button_sign'];
            $creditcards[$key]['bill_sign'] = $val['bill_sign'];
            $creditcards[$key]['differ_day'] = $val['differ_day'];
            $creditcards[$key]['bill_money'] = $val['bill_money'];
        }
        return $creditcards ? $creditcards : [];
    }

    /**
     * 格式化日期
     * 01/10
     * @param string $param
     * @return string
     */
    public static function formatBillDate($param = '')
    {
        if (empty($param)) {
            return '--';
        }
        $params = explode('-', $param);
        $res = $params[1] . '/' . $params[2];
        return $res ? $res : '';
    }

    /**
     * 账单导入网易列表logo数据处理
     * @param array $importData
     * @return array
     */
    public static function getImportCyberBanks($importData = [])
    {
        foreach ($importData as $key => $value) {
            $importData[$key]['bank_logo'] = QiniuService::getImgs($value['bank_logo']);
        }

        return $importData ? $importData : [];
    }

    /**
     * 导入结果页 日期处理
     * @param array $params
     * @return array
     */
    public static function getBillImportResults($params = [])
    {
        foreach ($params as $key => $val) {
            if ($val['bills']) {
                foreach ($val['bills'] as $k => $item) {
                    $params[$key]['bills'][$k]['bank_bill_time'] = DateUtils::formatTimeToYm($item['bank_bill_time']);
                }
            }
        }

        return $params ? $params : [];
    }

    /**
     * 网贷详情页数据处理
     * @param array $billInfos
     * @param array $product
     * @return array
     */
    public static function getProductInfo($billInfos = [], $product = [])
    {
        $datas = [];
        foreach ($billInfos as $key => $item) {
            $datas[$key]['billProductId'] = $product['id'];
            $datas[$key]['id'] = $item['id'];
            $datas[$key]['repay_time'] = DateUtils::formatTimeToYmdBySpot($item['repay_time']);
            $datas[$key]['period_num'] = $item['product_bill_period_num'] . '/' . $product['product_period_total'];
            $datas[$key]['bill_money'] = $item['bill_money'];
            $datas[$key]['bill_status'] = $item['bill_status'];

            //当前时间
            $now = date('Y-m-d', time());
            $strto_now = strtotime($now);
            //还款日期
            $repay_time = $item['repay_time'];
            $strto_repay_time = strtotime($repay_time);
            //账单日
            $bill_time = $item['bank_bill_time'];
            $stro_bill_time = strtotime($bill_time);

            //bill_status 每期状态 【0待还，1已还，2未还，9逾期】
            if ($item['is_import'] == 0 && $item['bill_status'] != 1) {
                //账单日 < now < 还款日
                if ($strto_now >= $stro_bill_time && $strto_now <= $strto_repay_time) {
                    $datas[$key]['bill_status'] = 0;
                }
                if ($strto_now > $strto_repay_time) {
                    //逾期、设为已还、1000
                    $datas[$key]['bill_status'] = 9;
                }
            }

        }

        return $datas ? $datas : [];
    }

    /**
     * 账单管理 —— 网贷数据处理
     * @param array $products
     * @return array
     */
    public static function getManageProducts($products = [])
    {
        $datas = [];
        foreach ($products as $key => $item) {
            $datas[$key]['bill_platform_id'] = $item['id'];
            $datas[$key]['product_id'] = $item['product_id'];
            $datas[$key]['product_name'] = $item['product_name'];
            $datas[$key]['total_money'] = DateUtils::formatDataToBillion($item['total_money']);
            $datas[$key]['product_logo'] = QiniuService::getImgToBillProduct();
            $datas[$key]['create_at'] = DateUtils::formatDateToLeftdata($item['created_at']);
        }

        return $datas;
    }

    /**
     * 账单管理 —— 信用卡列表
     * @param array $creditcards
     * @return array
     */
    public static function getCreditcardManages($creditcards = [])
    {
        $datas = [];
        foreach ($creditcards as $key => $value) {
            $datas[$key]['id'] = $value['id'];
            $datas[$key]['bank_conf_id'] = $value['bank_conf_id'];
            $datas[$key]['bank_credit_card_num'] = $value['bank_credit_card_num'];
            $datas[$key]['bank_name_on_card'] = $value['bank_name_on_card'];
            $datas[$key]['bank_short_name'] = isset($value['bankInfo']['bank_short_name']) ? $value['bankInfo']['bank_short_name'] : '';
            $datas[$key]['bank_logo'] = isset($value['bankInfo']['bank_logo']) ? QiniuService::getImgs($value['bankInfo']['bank_logo']) : '';
            $datas[$key]['bank_watermark_link'] = isset($value['bankInfo']['bank_watermark_link']) ? QiniuService::getImgs($value['bankInfo']['bank_watermark_link']) : '';
            $datas[$key]['bank_bg_color'] = isset($value['bankInfo']['bank_bg_color']) ? UserBillPlatformStrategy::getBankBgColor($value['bankInfo']['bank_bg_color']) : '';
            $datas[$key]['is_hidden'] = $value['is_hidden'];
            $datas[$key]['bank_is_import'] = $value['bank_is_import'];
            //按钮 【0默认，1设为已还,2更新账单可点击,3更新账单不可点击】
            $datas[$key]['bill_sign'] = isset($value['bill_sign']) ? $value['bill_sign'] : 0;
            $datas[$key]['button_sign'] = isset($value['button_sign']) ? $value['button_sign'] : 0;
            $datas[$key]['bank_bill_time'] = DateUtils::formatTimeToMd($value['bank_bill_time']);
            $datas[$key]['repay_time'] = DateUtils::formatTimeToMd($value['repay_time']);
        }

        return $datas ? $datas : [];
    }

    /**
     * 银行卡背景色
     * @param string $color
     * @return string
     */
    public static function getBankBgColor($color = '')
    {
        $color = trim($color);
        if (strlen($color) == 6) {
            return $color;
        }

        return UserBillPlatformConstant::BANK_BG_DEFAULT_COLOR;
    }
}