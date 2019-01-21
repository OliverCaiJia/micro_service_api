<?php

namespace App\Models\Factory;

use App\Constants\EventConstant;
use App\Models\AbsModelFactory;
use App\Models\Orm\Event;
use App\Models\Orm\EventHeap;
use App\Models\Orm\EventLog;

/**
 * Class EventFactory
 * @package App\Models\Factory
 * 事件弹窗工厂
 */
class EventFactory extends AbsModelFactory
{
    /**
     * @param $userId
     * @return int
     * 事件日志中记录次数
     */
    public static function fetchEventLogNum($userId)
    {
        $eventLog = EventLog::select(['num'])
            ->where(['event_id' => 999, 'user_id' => $userId])
            ->first();

        return $eventLog ? $eventLog->num : 0;
    }

    /**
     * @return int
     * 事件弹窗可以出发的最大次数
     */
    public static function fetchEventNum()
    {
        $event = Event::select(['max_num'])
            ->where(['id' => 999])
            ->first();

        return $event ? $event->max_num : 0;
    }

    /**
     * @return array
     * 查询推送事件
     */
    public static function fetchEvent()
    {
        $event = Event::select()
            ->where(['id' => EventConstant::EVENT_PUT_ID])
            ->first();

        return $event ? $event->toArray() : [];
    }

    /**
     * @param $score_id
     * @return array
     *  查询信用资料填写完整的推送事件内容
     */
    public static function fetchEventMessageArray($param)
    {
        $eventMessage = EventHeap::select()
            ->where(['id' => $param])
            ->first();

        return $eventMessage ? $eventMessage->toArray() : [];
    }

    /**
     * @param array $params
     * @return mixed
     * 推送日志记录表（添加）
     */
    public static function createEventLog($params = [])
    {
        $eventLog = EventLog::firstOrCreate(
            [
                'user_id' => $params['user_id'],
                'event_id' => EventConstant::EVENT_PUT_ID,
            ],
            [
                'user_id' => $params['user_id'],
                'event_id' => EventConstant::EVENT_PUT_ID,
                'create_time' => time(),
                'username' => $params['username'],
                'telephone' => $params['mobile'],
                'num' => 1,
            ]);
        $eventLog->event_id = EventConstant::EVENT_PUT_ID;
        $eventLog->user_id = $params['user_id'];
        $eventLog->create_time = time();
        $eventLog->num += 1;
        return $eventLog->save();
    }


}