<?php

namespace App\Strategies;

use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;

/**
 * 公共策略
 *
 * @package App\Strategies
 */
class HelpStrategy extends AppStrategy
{
    /**
     * @param array $params
     * @return array
     * 帮助中心图片处理
     */
    public static function getHelps($params = [])
    {
        foreach ($params as $key => $val) {
            $params[$key]['img_link'] = QiniuService::getImgs($val['img_link']);
        }

        return $params;
    }
}
