<?php

namespace App\Http\Controllers\V2;

use App\Helpers\RestResponseFactory;
use App\Http\Controllers\Controller;
use App\Models\Factory\UserReportFactory;
use App\Strategies\UserReportStrategy;
use Illuminate\Http\Request;

/**
 * 用户信用报告控制器
 * Class UserReportController
 * @package App\Http\Controllers\V1
 */
class UserReportController extends Controller
{
    /**
     * 报告订单列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchReports(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //分页
        $data['pageSize'] = $request->input('pageSize', 1);
        $data['pageNum'] = $request->input('pageNum', 10);
        //查询类型
        $data['payType'] = $request->input('payType', 0);

        $report = UserReportFactory::fetchReports($data);
        $pageCount = $report['pageCount'];
        //暂无数据
        if (empty($report['list'])) {
            $report['list'][] = [
                'id' => 0,
                'user_id' => 0,
                'serial_num' => 'SD171222***',
                'pay_type' => 0,
                'step' => 0,
                'end_time' => '',
                'front_serial_num' => 'SD170628***',
                'start_time' => date('Y-m-d', strtotime(" -10 day")),
                'username' => '王**',
                'idcard' => '263300********0666',
                'step_sign' => 4,
            ];
            $report['pageCount'] = 1;
        } else {
            //数据处理
            $report['list'] = UserReportStrategy::getReports($report['list']);
            $report['pageCount'] = $pageCount;
        }

        return RestResponseFactory::ok($report);
    }

}