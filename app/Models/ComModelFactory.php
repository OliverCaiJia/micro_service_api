<?php

namespace App\Models;

use App\Helpers\Logger\SLogger;
use App\Models\Orm\DeliveryCount;
use App\Models\Orm\DeliveryLog;

class ComModelFactory
{

    /**
     * Instantiate a new Controller instance.
     */
    public function __construct()
    {
        date_default_timezone_set('Asia/Shanghai'); //时区配置
    }

    /**
     * @param $register
     * 渠道端口数量统计————首页
     * 1 ,点击 2 ，注册
     */
    public static function channelVisitStatistics($channel_fr)
    {
        //总量统计 visit
        $re = DeliveryCount::where('nid', '=', $channel_fr)->first();
        if ($re)
        {
            $re->visit += 1;
            return $re->save();
        }
        return false;
    }

    /**
     * @param $param
     * @return bool
     * 渠道流水添加
     */
    public static function createDeliveryLog($param)
    {
        $re = DeliveryCount::where('nid', '=', $param)->first();
        if ($re)
        {
            // 历史原因 channel_nid 和 channel_id值记录反了
            $channel = new DeliveryLog();
            $channel->channel_id = $param;
            $channel->channel_nid = $re->id;
            $channel->create_time = time();
            $channel->type = 1;
            $channel->user_id = 0;
            $channel->client_type = 3;
            $channel->create_date = date('Y-m-d H:i:s', time());
            return $channel->save();
        }
        return false;
    }

    /**
     * 将\r、\t、\n 去掉
     * @param $str
     * @return mixed|string
     */
    public static function escapeHtml($str)
    {
        $arr = array("\n", "\r", "\t");
        $str = str_replace($arr, "", $str);
        return $str ? $str : '';
    }

}
