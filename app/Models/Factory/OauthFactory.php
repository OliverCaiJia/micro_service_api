<?php

namespace App\Models\Factory;

use App\Helpers\UserAgent;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\DataProductAccessLog;
use App\Models\Orm\PlatformProduct;
use App\Models\Orm\UserAuth;
use App\Services\Core\Platform\PlatformService;

class OauthFactory extends AbsModelFactory
{
    //判断平台开关
    public static function checkChannelStatus($id, $channelStatus)
    {
        $products = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.platform_product_id' => $id])
            ->select(['p.channel_status as product_channel_status'])
            ->addSelect(['pf.channel_status'])
            ->where(['pf.channel_status' => $channelStatus])
            ->first();

        return $products ? $products->toArray() : [];
    }

    /**
     * @param $id
     * @param $channelStatus
     * @return array
     * 判断产品开关
     */
    public static function checkProductChannelStatus($id, $channelStatus)
    {
        $products = PlatformProduct::from('sd_platform_product as p')
            ->join('sd_platform as pf', 'p.platform_id', '=', 'pf.platform_id')
            ->where(['p.is_delete' => 0, 'pf.online_status' => 1, 'pf.is_delete' => 0])
            ->where(['p.platform_product_id' => $id])
            ->select(['p.channel_status as product_channel_status'])
            ->addSelect(['pf.channel_status'])
            ->where(['p.channel_status' => $channelStatus])
            ->first();

        return $products ? $products->toArray() : [];
    }

    /**
     * @param $logData
     * 对接平台返回流水
     */
    public static function createDataProductAccessLog($datas = [])
    {
        //dd($datas);
        $log = new DataProductAccessLog();
        $log->user_id = $datas['userId'];
        $log->username = $datas['username'];
        $log->mobile = $datas['mobile'];
        $log->platform_id = $datas['platformId'];
        $log->platform_product_id = $datas['productId'];
        $log->platform_product_name = $datas['product']['product_name'];
        $log->is_new_user = $datas['is_new_user'];
        $log->complete_degree = isset($datas['complete_degree']) ? $datas['complete_degree'] :  '';
        $log->qualify_status = isset($datas['qualify_status']) ? $datas['qualify_status'] : '0';
        $log->apply_url = $datas['apply_url'];
        $log->feedback_message = $datas['feedback_message'];
        $log->channel_no = $datas['channel_no'];
        $log->user_agent = UserAgent::i()->getUserAgent();
        $log->create_at = date('Y-m-d H:i:s', time());
        $log->create_ip = Utils::ipAddress();
        return $log->save();
    }

}