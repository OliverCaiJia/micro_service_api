<?php

namespace App\Strategies;

use App\Helpers\Logger\SLogger;
use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * Class PushStrategy
 * @package App\Strategies
 * 推送 & 任务弹窗 策略层
 */
class PushStrategy extends AppStrategy
{
    /**
     * @param $push
     * @return array
     * 任务弹窗数据处理
     */
    public static function getPopup($push)
    {
        $pushArr = [];
        $pushArr['name'] = $push['name'];
        $pushArr['push_times'] = $push['push_times'];
        $pushArr['description'] = $push['description'];
        $pushArr['url'] = $push['url'];
        $pushArr['img'] = QiniuService::getImgs($push['img']);

        return $pushArr;
    }

    /**
     * 批量弹窗数据格式整理
     * @param $push
     * @return array
     */
    public static function getPopups($push)
    {
        $pushArr = [];
        foreach ($push as $key => $val) {
            $pushArr[$key]['id'] = $val['id'];
            $pushArr[$key]['name'] = $val['name'];
            $pushArr[$key]['push_times'] = $val['push_times'];
            $pushArr[$key]['description'] = $val['description'];
            $pushArr[$key]['url'] = $val['url'];
            $pushArr[$key]['img'] = QiniuService::getImgs($val['img']);
        }

        return $pushArr;
    }

    /**
     * @param $launchAd
     * 启动页广告
     */
    public static function getLaunchAd($launchAd)
    {
        $launchAd['img'] = QiniuService::getImgs($launchAd['img']);
        return $launchAd;
    }

    /**
     * 获取手机平台类型
     *
     * @return int
     */
    public static function getPlatformType()
    {
        $userAgent = UserAgent::i()->getUserAgent();
        if (preg_match('/Mozilla/', $userAgent)) {
            return $type = 7;
        }

        if (Utils::isAndroid()) {
            $type = 2;
        } else if (Utils::isiOS()) {
            $type = 1;
        } else {
            $type = 7;
        }

        return $type;
    }

    /**
     * @param $params
     * @return mixed
     * @type 1 ios,2 android
     * 处理插入sd_user_jpush表中的数据
     */
    public static function getRegistrations($params)
    {
        if (Utils::isiOS()) {
            $datas['type'] = 1;
        } elseif (Utils::isAndroid()) {
            $datas['type'] = 2;
        } else {
            $datas['type'] = 1;
        }
        $datas['registration_id'] = $params['registrationId'];
        $datas['user_id'] = $params['userId'];
        $datas['agent'] = UserAgent::i()->getUserAgent();

        return $datas;
    }

}