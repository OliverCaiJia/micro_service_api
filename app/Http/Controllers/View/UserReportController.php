<?php

namespace App\Http\Controllers\View;

use App\Constants\UserIdentityConstant;
use App\Constants\UserReportConstant;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\UserReport\CreditIndustry\DoCreditIndustryHandler;
use App\Models\Chain\UserReport\ReportScore\DoReportScoreHandler;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserReportFactory;
use App\Strategies\UserIdentityStrategy;
use App\Strategies\UserReportStrategy;
use Illuminate\Http\Request;

/**
 * 用户信用报告
 * Class UserController
 * @package App\Http\Controllers\View
 */
class UserReportController extends Controller
{
    /**
     * 信用报告详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function fetchReportinfo(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //$data['userId'] = 1288;
        //付费类型
        $data['reportTaskId'] = $request->input('reportTaskId', 0);
        //根据报告任务id查询报告
        $data['report_task_id'] = $data['reportTaskId'];
        //详情
        $info = UserReportFactory::fetchReportinfoById($data);
        //数据采集中
        if (!$info) {
            $error_meg = RestUtils::getErrorMessage(13006);
            return view('app.jdt.errors.error_static', ['error' => $error_meg]);
        }

        //得分为0时重新计算
        if (empty($info['final_score'])) {

            //根据报告id，根据速贷之家规则生成报告数据
            $data['reportinfo'] = $info;
            $res = new DoCreditIndustryHandler($data);
            $res = $res->handleRequest();
            if (isset($res['error'])) {
                $error_meg = RestUtils::getErrorMessage(13006);
                return view('app.jdt.errors.error_static', ['error' => $error_meg]);
            }

            //计算得分
            $res = new DoReportScoreHandler($data);
            $res = $res->handleRequest();
            if (isset($res['error'])) {
                $error_meg = RestUtils::getErrorMessage(13006);
                return view('app.jdt.errors.error_static', ['error' => $error_meg]);
            }
            //详情
            $info = UserReportFactory::fetchReportinfoById($data);
        }

        //身份证号
        $data['face_status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FINAL;
        $user = UserIdentityFactory::fetchIdcardinfoById($data);
        $info['idcard'] = isset($user['certificate_no']) ? UserIdentityStrategy::formatCertificateNoFour($user['certificate_no']) : '';
        //数据处理
        $info = UserReportStrategy::getReportinfoById($info);
        $title = UserReportConstant::REPORT_INFO;
        return view('app.jdt.credit_report.info', ['info' => $info, 'title' => $title]);
    }

    /**
     * 信用报告——个人报告查询授权书
     * @return \Illuminate\View\View
     */
    public function fetchReportAgreement()
    {
        return view('app.jdt.credit_report.credit_report_agreement');
    }

    /**
     * 信用报告样本
     * @return \Illuminate\View\View
     */
    public function fetchReportSample()
    {
        $title = UserReportConstant::REPORT_SAMPLE;
        return view('app.jdt.credit_report.report_sample', ['title' => $title]);
    }

    /**
     * 速贷之家自定义芝麻跳转地址
     * @return \Illuminate\View\View
     */
    public function fetchZhimaUrl()
    {
        return view('app.jdt.credit_report.zhima_jump');
    }
}
