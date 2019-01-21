<?php

namespace App\Listeners\Oneloan\Partner\Callback;

use App\Models\Factory\UserSpreadFactory;
use App\Strategies\SpreadStrategy;

/**
 *  猴哥贷回调
 */
class HougedaiCallback
{

    /**
     * 猴哥贷接口响应数据处理
     * @param $res
     * @return array
     */
    public static function handleRes($res, $spread)
    {
        //处理结果
        $spread['result'] = json_encode($res, JSON_UNESCAPED_UNICODE);
        $spread['status'] = 0;
        $spread['message'] = '数据为空';
        $spread['response_code'] = 0;

        if (isset($res['resultCode']) && $res['resultCode'] == '0') {
            $spread['status'] = 1;
            $spread['message'] = isset($res['Msg']) ? $res['Msg'] : '成功';
            $spread['response_code'] = 1;
        } else {
            $spread['status'] = 0;
            $spread['message'] = isset($res['Msg']) ? $res['Msg'] : '失败';
            $spread['response_code'] = 2;
        }

        if (!UserSpreadFactory::checkIsSpread($spread)) {
            $spread['id'] = UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        } else {
            // 更新分发数据状态
            UserSpreadFactory::insertOrUpdateUserSpreadLog($spread);
        }
        // 更新推送次数等数据
        SpreadStrategy::updateSpreadCounts($spread);

        return $spread;
    }

}
