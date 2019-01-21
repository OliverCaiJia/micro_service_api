<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserCreditStatus;

/**
 * Class CreditStatusFactory
 * @package App\Models\Factory
 * 积分状态工厂类
 */
class CreditStatusFactory extends AbsModelFactory
{
    /**
     * @param $params
     * @return int
     * 一次性完成任务状态  完成标识1 未完成标识0
     */
    public static function fetchCreditStatusByUserId($params = [])
    {
        $status = UserCreditStatus::select(['id'])
            ->where(['user_id' => $params['userId'], 'type_id' => $params['typeId']])
            ->first();

        return $status ? 1 : 0;
    }

    /**
     * @param array $params
     * @return bool
     * 一次性完成任务状态  如果存在返回false 不存在返回true
     */
    public static function fetchCreditOnceStatusByUserId($params = [])
    {
        $status = UserCreditStatus::select(['id'])
            ->where(['user_id' => $params['userId'], 'type_id' => $params['typeId']])
            ->first();

        return $status ? false : true;
    }

    /**
     * @param array $params
     * @return bool
     * 一次性加完积分 例如：更换用户名
     */
    public static function updateCreditStatusById($params = [])
    {
        $query = UserCreditStatus::firstOrCreate(['user_id' => $params['userId'], 'type_id' => $params['typeId']], [
            'user_id' => $params['userId'],
            'type_id' => $params['typeId'],
            'count' => 1,
            'remark' => $params['remark'],
            'updated_at' => date('Y-m-d', time()),
            'updated_ip' => Utils::ipAddress(),
        ]);

        $query->updated_at = date('Y-m-d', time());
        $query->updated_ip = Utils::ipAddress();
        return $query->save();
    }

    /**
     * @param array $params
     * @return bool
     * 多次性累计加分 例如：评论
     */
    public static function updateCreditStatusCountById($params = [])
    {
        $time = date('Y-m-d', time());
        $query = UserCreditStatus::firstOrCreate(['user_id' => $params['userId'], 'type_id' => $params['typeId'], 'updated_at' => $time], [
            'user_id' => $params['userId'],
            'type_id' => $params['typeId'],
            'count' => 1,
            'remark' => $params['remark'],
            'updated_at' => date('Y-m-d', time()),
            'updated_ip' => Utils::ipAddress(),
        ]);

        $query->count += 1;
        $query->updated_at = date('Y-m-d', time());
        $query->updated_ip = Utils::ipAddress();
        return $query->save();
    }

    /**
     * @param array $params
     * @return int|mixed
     * 获取每天的次数
     */
    public static function fetchCreditStatusCountById($params = [])
    {
        $time = date('Y-m-d', time());
        $count = UserCreditStatus::select(['count'])
            ->where(['user_id' => $params['userId'], 'type_id' => $params['typeId']])
            ->where(['updated_at' => $time])
            ->first();

        return $count ? $count->count : 0;

    }

    /** 查询状态是否存在
     * @param array $params
     * @return int|mixed
     *
     */
    public static function fetchCreditStatusById($params = [])
    {
        $status = UserCreditStatus::select(['count'])
            ->where(['user_id' => $params['userId'], 'type_id' => $params['typeId']])
            ->first();

        return $status ? $status->toArray() : [];
    }

    /**当天统计总次数不存在，将count清0
     * @param $params
     * @return bool
     *
     */
    public static function updateCreditCountById($params)
    {
        return UserCreditStatus::where(['user_id' => $params['userId'], 'type_id' => $params['typeId']])
            ->update(['count' => 0, 'updated_at' => date('Y-m-d', time()), 'updated_ip' => Utils::ipAddress()]);
    }
}