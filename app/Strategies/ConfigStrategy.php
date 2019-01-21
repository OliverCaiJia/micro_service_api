<?php

namespace App\Strategies;

use App\Strategies\AppStrategy;

/**
 * 配置公共策略
 *
 * @package App\Strategies
 */
class ConfigStrategy extends AppStrategy
{

    /**
     * 获取对接平台的Appkey
     * @param array $params
     * @return array
     */
    public static function getAbutPlatformAppkey($params = [])
    {
        $datas = [];
        $datas['scorpio'] = $params['scorpioKey'];
        $datas['ppd'] = isset($params['ppdKey']) ? $params['ppdKey'] : [];

        return $datas ? $datas : [];
    }
}