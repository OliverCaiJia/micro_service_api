<?php

namespace App\Models\Factory;

use App\Constants\ProductConstant;
use App\Models\AbsModelFactory;
use App\Models\Orm\SystemConfig;
use App\Models\Orm\SystemBatchConfig;

/**
 * @package App\Models\Factory
 */
class SystemFactory extends AbsModelFactory
{

    /**
     *  批处理短信发送间隔
     * @return type
     */
    public static function getBatchInterval()
    {
        $config = SystemBatchConfig::select('value')->where('nid', '=', 'con_user_batch_sms_interval')->where('status', '=', '1')->first();
        return $config ? $config->value : 3600;
    }

    /**
     *  批处理短信发送次数
     * @return type
     */
    public static function getBatchTimes()
    {
        $config = SystemBatchConfig::select('value')->where('nid', '=', 'con_user_batch_sms_times')->where('status', '=', '1')->first();
        return $config ? $config->value : 2;
    }

    /**
     * 批处理短信内容
     * @param string $type
     * @return string
     */
    public static function getBatchMessage($type = '')
    {
        $config = SystemBatchConfig::select('value')->where('nid', '=', 'con_user_batch_sms_' . $type)->where('status', '=', '1')->first();
        return $config ? $config->value : '';
    }

    /**
     * 批处理短信开关
     * @return type
     */
    public static function getBatchSwitch()
    {
        $config = SystemBatchConfig::select('value')->where('nid', '=', 'con_user_batch_sms_switch')->where('status', '=', '1')->first();
        return $config ? $config->value : 0;
    }

    /**
     * @param $onlineConfigNid
     * @return string
     * 根据nid获取value值
     */
    public static function fetchProductOnlineRemark($onlineConfigNid)
    {
        $value = SystemConfig::select(['value'])
            ->where(['nid' => $onlineConfigNid, 'status' => 1])
            ->first();

        return $value ? $value->value : ProductConstant::PRODUCT_ONLINE_REMARK;
    }

}
