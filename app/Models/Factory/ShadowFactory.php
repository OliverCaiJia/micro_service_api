<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\ShadowCount;
use App\Models\Orm\ShadowLog;
use App\Models\Orm\UserShadow;

/**
 * Class ShadowFactory
 * @package App\Models\Factory
 * 马甲工厂
 */
class ShadowFactory extends AbsModelFactory
{
    /**根据shadow_nid获取id
     * @param $nid
     * @return int|mixed
     *
     */
    public static function fetchIdByShadowNid($nid)
    {
        $id = ShadowCount::select(['id'])
            ->where(['nid' => $nid])->first();

        return $id ? $id->id : 0;
    }

    /**判断表sd_user_shadow中是否存在唯一的shadow_id与user_id
     * @param array $params
     * @return array
     */
    public static function checkUserShadow($params = [])
    {
        $check = UserShadow::select(['id'])
            ->where(['user_id' => $params['user_id'], 'shadow_id' => $params['shadow_id']])
            ->first();

        return $check ? $check->toArray() : [];
    }

    /**
     * @param array $params
     * @return bool
     * 创建马甲流水
     */
    public static function createShadowLog($params = [])
    {
        $log = new ShadowLog();
        $log->shadow_id = $params['shadow_id'];
        $log->shadow_nid = $params['shadow_nid'];
        $log->user_id = $params['user_id'];
        $log->terminal_type = $params['terminal_type'];
        $log->created_at = date('Y-m-d H:i:s', time());
        $log->created_ip = Utils::ipAddress();
        return $log->save();
    }

    /**
     * @param array $params
     * @return bool
     * 向表sd_user_shadow中插入数据
     */
    public static function createUserShadow($params = [])
    {
        $shadow = new UserShadow();
        $shadow->user_id = $params['user_id'];
        $shadow->shadow_id = $params['shadow_id'];
        $shadow->delivery_id = $params['delivery_id'];
        $shadow->terminal_type_id = $params['terminal_type'];
        $shadow->is_new = $params['is_new'];
        $shadow->created_at = date('Y-m-d H:i:s', time());
        $shadow->created_ip = Utils::ipAddress();
        return $shadow->save();
    }

    /**
     * @param array $params
     * @return bool
     * 更新sd_shadow_count表中注册总量
     */
    public static function updateShadowCount($nid)
    {
        $shadow = ShadowCount::where(['nid' => $nid])
            ->first();
        $shadow->increment('register', 1);
        $shadow->updated_at = date('Y-m-d H:i:s', time());
        $shadow->updated_ip = Utils::ipAddress();
        return $shadow->save();
    }

    /** 根据nid获取shadow信息
     * @param $nid
     * @return array
     */
    public static function getShadow($nid)
    {
        $shadow = ShadowCount::where('nid', $nid)->first();
        return $shadow ? $shadow->toArray() : [];
    }

    /** 获取渠道id
     * @param $userId
     * @return int|mixed
     */
    public static function getDeliveryId($userId)
    {
        $shadow = UserShadow::where('user_id', $userId)->select(['delivery_id'])
            ->first();
        return $shadow ? $shadow->delivery_id : 0;
    }

    /** 获取shadow id
     * @param $userId
     * @return mixed|string
     */
    public static function getShadowId($userId)
    {
        $shadow = UserShadow::where('user_id', $userId)->first();
        return $shadow ? $shadow->shadow_id : '';
    }

    /** 获取shadow的nid
     * @param $id
     * @return mixed|string
     */
    public static function getShadowNid($id)
    {
        $shadow = ShadowCount::where('id', $id)->first();
        return $shadow ? $shadow->nid : '';
    }

}