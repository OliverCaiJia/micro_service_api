<?php

namespace App\Strategies;

use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * Class BanksStrategy
 * @package App\Strategies
 * 拥有信用卡的银行策略
 */
class BanksStrategy extends AppStrategy
{
    /**
     * @param $hots
     * @return mixed
     * 银行logo数据转化
     */
    public static function getBankLogo($datas)
    {
        foreach ($datas as $key => $val) {
            $datas[$key]['bank_logo'] = QiniuService::getBankLogo($val['bank_logo']);
        }

        return $datas;
    }

    /**
     * @param $banks
     * @return mixed
     * 根据银行id获取单个银行信息转化
     */
    public static function getBanksById($banks)
    {
        $datas['id'] = $banks['id'];
        $datas['bank_short_name'] = $banks['bank_short_name'];
        $datas['bank_logo'] = QiniuService::getBankLogo($banks['bank_logo']);
        $datas['all_link'] = $banks['all_link'];
        $datas['pass_rate_type'] = self::formatPassRateType($banks['pass_rate_type']);
        $datas['approve'] = $banks['approve_min'] . '-' . $banks['approve_max'] . '天';
        $datas['bank_desc'] = $banks['bank_desc'];

        return $datas;

    }

    /**
     * @param $param
     * @return string
     * 0 低,1 较高, 2 高,3 极高
     */
    public static function formatPassRateType($param)
    {
        $i = intval($param);
        if ($i == 0) return '低';
        elseif ($i == 1) return '较高';
        elseif ($i == 2) return '高';
        elseif ($i == 3) return '极高';
        else return '';
    }

    /**
     * @param $bank
     * @return mixed
     * 提额银行所需内容
     */
    public static function getQuotaBankInfo($bank)
    {
        $datas['id'] = $bank['id'];
        $datas['quota_link'] = $bank['quota_link'];
        $datas['mobile_quota'] = $bank['mobile_quota'];
        $datas['service_mobile'] = $bank['service_mobile'];
        $datas['wechat_quota'] = $bank['wechat_quota'];
        $datas['sms_quota'] = $bank['sms_quota'];
        $datas['sms_content'] = $bank['sms_content'];
        $datas['quota_mobile'] = $bank['quota_mobile'];

        return $datas;
    }

    /**
     * @param $params
     * @return array
     * 立即提额数据转化
     */
    public static function getQuotas($params)
    {
        foreach ($params as $key => $val) {
            $params[$key]['bank_logo'] = QiniuService::getBankLogo($val['bank_logo']);
            if (empty($val['quota_link'])) {
                $params[$key]['is_quota_link'] = 0;
            } else {
                $params[$key]['is_quota_link'] = 1;
            }
        }

        return $params ? $params : [];
    }

    public static function getHasCreditcardBanks($params, $bankLists)
    {
        foreach ($bankLists as $key => $val) {
            foreach ($params as $k => $v) {
                $params[$key + 1]['id'] = $val['id'];
                $params[$key + 1]['bank_short_name'] = $val['bank_short_name'];
            }
        }

        return $params;
    }
}