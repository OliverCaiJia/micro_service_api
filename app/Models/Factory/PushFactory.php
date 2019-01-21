<?php

namespace App\Models\Factory;

use App\Helpers\Logger\SLogger;
use App\Models\AbsModelFactory;
use App\Models\Orm\Popup;
use App\Models\Orm\PopupType;
use App\Models\Orm\UserJpush;
use Illuminate\Support\Facades\Log;

/**
 * Class PushFactory
 * @package App\Models\Factory
 * 推送 & 任务弹窗
 */
class PushFactory extends AbsModelFactory
{
    /**
     * 任务弹窗
     * @version_code 任务弹窗版本标识  ''表示第一个版本，只显示''的内容
     * @param $position
     * @return array
     */
    public static function fetchPopup($position)
    {
        $push = Popup::select()
            ->where(['status' => 0, 'position' => $position, 'version_code' => ''])
            ->orderBy('update_time', 'desc')
            ->limit(1)
            ->first();

        return $push ? $push->toArray() : [];
    }

    /**
     * 批量弹窗
     * @param array $data
     * @return array
     */
    public static function fetchPopups($data = [])
    {
        $push = Popup::select()
            ->where(['status' => 0, 'position' => $data['position']])
            ->whereIn('version_code', $data['versionCode'])
            ->orderBy('position_sort', 'asc')
            ->get();

        return $push ? $push->toArray() : [];
    }

    /**
     * @param $id
     * @return mixed
     * 任务弹窗 —— 执行次数叠加
     */
    public static function updatePopup($id)
    {
        $popupIncre = Popup::where(['id' => $id])
            ->increment('do_count', 1);

        return $popupIncre;
    }

    /**
     * @param $position
     * 启动页广告
     */
    public static function fetchLaunchAd($position)
    {
        $launchAd = Popup::select(['id', 'url', 'img', 'name', 'description'])->where(['position' => $position, 'status' => 0])
            ->orderBy('update_time', 'desc')
            ->limit(1)
            ->first();
        return $launchAd ? $launchAd->toArray() : [];
    }

    /**
     * @param $position
     * 启动页广告执行次数
     */
    public static function updateDoCounts($id)
    {
        $launchAd = Popup::where(['id' => $id, 'status' => 0])
            ->orderBy('update_time', 'desc')
            ->limit(1)
            ->increment('do_count', 1);
        return $launchAd;
    }

    /**
     * 将极光推送的registration_id放入到数据库
     *
     * @param $data array
     * @return bool
     */
    public static function addJpushInfo($data = [])
    {
        $regId = UserJpush::where('registration_id', $data['registration_id'])->value('registration_id');
        if (!$regId) {
            $jpush = new UserJpush();
            $jpush->registration_id = $data['registration_id'];
            $jpush->terminal_type_id = $data['type'];
            $jpush->user_id = $data['user_id'] ?: 0;
            $jpush->created_at = date('Y-m-d H:i:s', time());
            $jpush->user_agent = $data['agent'];

            return $jpush->save();
        }

        return UserJpush::where('registration_id', $data['registration_id'])->update(['user_id' => $data['user_id'], 'updated_at' => date('Y-m-d H:i:s', time())]);
    }

    /**
     * @param $registrationId
     * @return string
     * 获取极光推送唯一设备记录表中的id
     */
    public static function fetchIdByRegistrationId($registrationId)
    {
        $registrationId = UserJpush::select(['id'])
            ->where(['registration_id' => $registrationId])
            ->first();

        return $registrationId ? $registrationId->id : 0;
    }

    /**
     * 获取推送图片
     * @is_default 默认, 1是 0否
     * @status 0  启用  1 关闭
     * @param array $params
     * @return array
     */
    public static function fetchGuidePageByType($params = [])
    {
        $push = Popup::select(['id', 'name', 'push_times', 'url', 'description', 'img'])
            ->where(['status' => 0, 'position' => $params['position'], 'width' => $params['width'], 'height' => $params['height'], 'is_default' => 0])
            ->orderBy('update_time', 'desc')
            ->limit(1)
            ->first();

        return $push ? $push->toArray() : [];
    }

    /**
     * 引导页广告默认图片
     * @param array $params
     * @return array
     */
    public static function fetchGuidePageByIsDefault($params = [])
    {
        $push = Popup::select(['id', 'name', 'push_times', 'url', 'description', 'img'])
            ->where(['status' => 0, 'position' => $params['position'], 'is_default' => $params['is_default']])
            ->orderBy('update_time', 'desc')
            ->limit(1)
            ->first();

        return $push ? $push->toArray() : [];
    }

}