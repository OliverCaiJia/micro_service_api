<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserZhima;
use App\Models\Orm\UserZhimaTask;

/**
 * 版本升级
 */
class ZhimaFactory extends AbsModelFactory
{

    /**
     * 根据姓名和身份证获取Openid
     * @param $data
     * @return array
     */
    public static function fetchOpenId($userId = 0)
    {
        $model = UserZhima::where('user_id', $userId)->first();
        return $model ? $model->open_id : '';
    }

    /** 根据openId 获取芝麻信用分数
     * @param string $openId
     * @return int
     */
    public static function getOldScore($openId = '')
    {
        if (!empty($openId))
        {
            $model = UserZhima::where('open_id', $openId)->first();
            return $model ? $model->score : 0;
        }

        return 0;
    }

    /** 创建芝麻任务
     * @param array $data
     * @return bool
     */
    public static function createZhimaTask($data = [])
    {
        $task = new UserZhimaTask();
        $task->user_id = $data['userId'];
        $task->status = 1;
        $task->step = 0;
        $task->created_at = date('Y-m-d H:i:s', time());
        $task->updated_at = date('Y-m-d H:i:s', time());
        $task->created_ip = Utils::ipAddress();
        $task->updated_ip = Utils::ipAddress();

        $res = $task->save();
        return $res;
    }

    /** 更新任务状态
     * @param array $data
     */
    public static function updateTaskStatus($data = [])
    {
        $before = date(strtotime('-30 day'));

        // 查询条件:当前用户+时间在30天以内+步骤为开始状态+状态为有效
        $task = UserZhimaTask::where('user_id', $data['userId'])->where('created_at', '>', $before)
            ->where('step', $data['where'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!empty($task))
        {
            $task->step = $data['step'];
            $task->updated_at = date('Y-m-d H:i:s', time());
            $task->updated_ip = Utils::ipAddress();
            return $task->save();
        }

        return false;

    }
}
