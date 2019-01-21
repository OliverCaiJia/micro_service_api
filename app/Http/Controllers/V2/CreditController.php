<?php

namespace App\Http\Controllers\V2;

use App\Helpers\RestResponseFactory;
use App\Http\Controllers\Controller;
use App\Models\Factory\CreditFactory;
use App\Helpers\RestUtils;
use App\Strategies\CreditStrategy;
use Illuminate\Http\Request;

/** 积分
 * Class CreditController
 * @package App\Http\Controllers\V1
 */
class CreditController extends Controller
{
    /** 获取积分列表
     * @param Request $request
     */
    public function fetchCreditIncome(Request $request)
    {
        // 参数
        $data['user_id'] = $userId = $request->user()->sd_user_id;
        // 0 积分消耗记录, 1 赚积分记录, 2 全部记录
        $data['status'] = $request->input('status');
        $data['pageSize'] = $request->input('pageSize');
        $data['pageNum'] = $request->input('pageNum');

        $creditArr = [];
        // 用户积分
        $userScore = CreditFactory::fetchCredit($userId);
        $creditArr['userScore'] = intval($userScore);
        // 用户积分流水
        $res = CreditFactory::fetchCreditLogs($data);

        if (empty($res['list']))
        {
            // 无数据返回
            $creditArr['has_data'] = 0;
            $creditArr['list'] = [];
            $creditArr['total'] = [];
            $creditArr['pageCount'] = 0;

            return RestResponseFactory::ok($creditArr);
        }
        else
        {
            // 有数据
            $creditArr['has_data'] = 1;
        }

        $creditArr['pageCount'] = $res['pageCount'];
        // 处理数据
        $creditArr['list'] = CreditStrategy::getCreditData($res['list'], $res['offset']);
        // 汇总数据
        $creditArr['total'] = CreditFactory::getCreditTotal($res['list'], $userId);

        return RestResponseFactory::ok($creditArr);
    }
}