<?php

namespace App\Strategies;

use App\Strategies\AppStrategy;

/**
 * Invite公共策略
 *
 * Class UserStrategy
 * @package App\Strategies
 */
class VersionStrategy extends AppStrategy
{
    /**
     * @param $versionName
     * @param array $versionData
     * Android —— 版本升级
     */
    public static function getVersionAndroid($versionName,$versionData = [])
    {
        $compare = version_compare($versionName, $versionData['version_code'], '<');
        if ($compare) {
            $is_upload = $versionData['type'];
        } else {
            $is_upload = 0;
        }
        $version                    = [];
        $version['is_upload'] = $is_upload;
        $version['version_code'] = $versionData['version_code'];
        $version['app_url'] = !empty($versionData['apk_url']) ? $versionData['apk_url'] : '';
        $version['upgrade_point'] = !empty($versionData['upgrade_point']) ? $versionData['upgrade_point'] : '';

        return $version;
    }

    /**
     * @param $versionName
     * @param array $versionData
     * @return array
     * Ios —— 版本升级
     */
    public static function getVersionIos($versionData = [])
    {
        $data = [];
        $data['type'] = empty($versionData['type']) ? 0 : $versionData['type'];
        $data['app_url'] = !empty($versionData['apk_url']) ? $versionData['apk_url'] : '';
        $data['upgrade_point'] = !empty($versionData['upgrade_point']) ? $versionData['upgrade_point'] : '';

        return $data;
    }
}
