<?php

namespace App\Strategies;

use App\Helpers\DateUtils;
use App\Services\AppService;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * Class CreditcardBannersStrategy
 * @package App\Strategies
 * 信用卡策略
 */
class CreditcardStrategy extends AppStrategy
{
    /**
     * @param $searches
     * @return mixed
     * 信用卡搜索数据转化
     */
    public static function getSearches($searches)
    {
        foreach ($searches as $key => $val) {
            $applyLogs['card_id'] = $val['id'];
            $applyLogs['card_h5_link'] = $val['card_h5_link'];
            $searches[$key]['card_logo'] = QiniuService::getBankLogo($val['card_logo']);
            $searches[$key]['total_apply_count'] = DateUtils::formatRound($val['total_apply_count']);
            $searches[$key]['card_h5_link'] = CreditcardStrategy::getCreditLocationUrl($applyLogs);
        }

        return $searches;
    }

    public static function getCreditCardSearches($data)
    {
        foreach ($data as $key => $val) {
            $data[$key]['card_logo'] = QiniuService::getBankLogo($val['card_logo']);
            $data[$key]['total_apply_count'] = DateUtils::formatRound($val['total_apply_count']);
        }

        return $data;
    }

    /**
     * @param $hots
     * @return mixed
     * 热词数据转化
     */
    public static function getHots($hots)
    {
        foreach ($hots as $key => $val) {
            $hots[$key]['product_name'] = $val['card_name'];
        }

        return $hots;
    }

    /**
     * @param $gifts
     * @return mixed
     * 办卡有礼产品图片
     */
    public static function getGiftsImgs($gifts)
    {
        foreach ($gifts as $key => $val) {
            $applyLogs['card_id'] = $val['id'];
            $applyLogs['card_h5_link'] = $val['card_h5_link'];
            $gifts[$key]['card_h5_link'] = CreditcardStrategy::getCreditLocationUrl($applyLogs);
            $gifts[$key]['card_img'] = QiniuService::getImgs($val['card_img']);
        }

        return $gifts;
    }

    /** 获取跳转链接
     * @param $data
     * @return string
     */
    public static function getCreditLocationUrl($data)
    {
        $cardId = $data['card_id'];
        $cardLink = $data['card_h5_link'];

        return AppService::API_URL . '/v1/data/credit/apply/log?card_id=' . $cardId . '&card_link=' . urlencode($cardLink);
    }
}