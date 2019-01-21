<?php

namespace App\MongoDB\Factory\Credit;

use App\MongoDB\Factory\MongoModelFactory;
use App\Services\Core\Credit\CreditService;

class ScorpioFactory extends MongoModelFactory
{
    /**
     * 更新事件状态
     *
     * @param $taskId
     * @param $status
     * @return bool
     */
    public static function updateEventStatus($taskId, $status)
    {
        //todo
        return true;
    }

    /**
     * 检查魔蝎的任务ID是否存在
     *
     * @param $taskId
     * @return bool
     */
    public static function isTaskId($taskId)
    {
        //todo
        return true;
    }

    /**
     * 对存入mongoDB的数据进行封装
     *
     * @param string|array $label 标签
     * @param array $data 接口返回的数据
     * @param array $params 接口需要的参数
     * @return array
     */
    public static function dataStruct($label, $data = [], $params = [])
    {
        return [
            'label' => $label,
            'data' => $data,
            'parmas' => $params,
        ];
    }

}