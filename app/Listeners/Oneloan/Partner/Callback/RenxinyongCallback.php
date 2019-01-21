<?php

namespace App\Listeners\Oneloan\Partner\Callback;

use App\Strategies\SpreadStrategy;
use App\Models\Factory\UserSpreadFactory;

/**
 *  任信用回调处理
 */
class RenxinyongCallback
{

    /**
     * 任信用回调处理
     * @param $res, $spread
     * @return array
     */
    public static function handleRes($res, $spread)
    {
        //处理结果
        $spread['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $spread['status'] = 0;
        $spread['message'] = '数据为空';
        $spread['response_code'] = 0;

        if (isset($res['code']) && $res['code'] == '200') {
            $spread['status'] = 1;
            $spread['message'] = isset($res['msg']) ? $res['msg'] : '成功';
            $spread['response_code'] = 1;
        } else {
            $spread['status'] = 0;
            $spread['message'] = isset($res['msg']) ? $res['msg'] : '失败';
            $spread['response_code'] = 2;
        }

        // 更新spreadLog
        if (!UserSpreadFactory::checkIsSpread($spread)) {
            $spread['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        } else {
            UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        }

        // 更新推送次数等数据
        SpreadStrategy::updateSpreadCounts($spread);

        return $spread;
    }

}