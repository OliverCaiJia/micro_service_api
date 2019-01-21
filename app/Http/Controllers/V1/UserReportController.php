<?php

namespace App\Http\Controllers\V1;

use App\Constants\UserIdentityConstant;
use App\Constants\UserReportConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Http\Controllers\Controller;
use App\Models\Chain\UserReport\Carrier\DoCarrierHandler;
use App\Models\Chain\UserReport\CreditIndustry\DoCreditIndustryHandler;
use App\Models\Chain\UserReport\ReportScore\DoReportScoreHandler;
use App\Models\Chain\UserReport\Zhima\DoZhimaHandler;
use App\Models\Factory\UserBankCardFactory;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserReportFactory;
use App\Models\Factory\UserVipFactory;
use App\Services\AppService;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Services\Core\Zhima\ZhimaService;
use App\Strategies\PaymentStrategy;
use App\Strategies\UserIdentityStrategy;
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
     * 免费查验证
     * @reportType integer 付费标识 1免费，2付费
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchFree(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //付费标识 1免费，2付费
        $data['payType'] = UserReportConstant::PAY_TYPE_FREE;
        //默认值 0
        $reportSign = UserReportConstant::PAY_TYPE_DEFAULT;

        //没有认证，先去认证
        $realname = UserIdentityFactory::fetchIdcardAuthenInfo($data);
        if (!$realname) {
            //12 表示未认证
            $reportSign = UserReportConstant::PAY_TYPE_NOT_ALIVE;
            return RestResponseFactory::ok(['report_sign' => $reportSign]);
        }

        //不是vip用户 只有vip用户才可以免费查询
        $vipTypeId = UserVipFactory::getVipTypeId();
        $vip = UserVipFactory::getVIPInfo($data['userId'], $vipTypeId);
        if (!$vip) {
            //11 表示不是vip 需要充值vip
            $reportSign = UserReportConstant::PAY_TYPE_NOT_VIP;
            return RestResponseFactory::ok(['report_sign' => $reportSign]);
        }

        //是vip 已经含有免费报告或含有进行中的报告 免费报告在有效期内进行中或已完成的只能有一份
        $data['step'] = UserReportConstant::REPORT_STEP_END;
        $report = UserReportFactory::fetchReportByIdAndType($data);
        if ($report && !empty($report['step'])) {
            $reportSign = UserReportStrategy::fetReportSign($report['step']);
            return RestResponseFactory::ok(['report_sign' => $reportSign]);
        }

        return RestResponseFactory::ok(['report_sign' => $reportSign]);
    }

    /**
     * 付费查验证
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchPay(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //付费标识 1免费，2付费
        $data['payType'] = UserReportConstant::PAY_TYPE_PAY;
        //默认值 0
        $reportSign = UserReportConstant::PAY_TYPE_DEFAULT;

        //未认证  只有认证过才可以进行付费查
        $realname = UserIdentityFactory::fetchIdcardAuthenInfo($data);
        if (!$realname) {
            //12 表示未认证
            $reportSign = UserReportConstant::PAY_TYPE_NOT_ALIVE;
            return RestResponseFactory::ok(['report_sign' => $reportSign]);
        }

        //付费进行中报告  4报告已生成，是否重新查询
        $data['payType'] = UserReportConstant::PAY_TYPE_PAY;
        $data['step'] = UserReportConstant::REPORT_STEP_END;
        //付费进行中报告
        $payreporting = UserReportFactory::fetchReportingByIdAndType($data);
        //已完成付费报告
        $payReport = UserReportFactory::fetchEfficationReportByIdAndStep($data);
        //有效免费报告不存在 并且为vip用户
        $data['payType'] = UserReportConstant::PAY_TYPE_FREE;
        //免费进行中报告
        $reporting = UserReportFactory::fetchReportingByIdAndType($data);
        //已完成免费报告
        $report = UserReportFactory::fetchEfficationReportByIdAndStep($data);
        //vip用户
        $vipTypeId = UserVipFactory::getVipTypeId();
        $vip = UserVipFactory::getVIPInfo($data['userId'], $vipTypeId);

        if ($payreporting && $payreporting['step'] != 4) {
            //付费的有效报告已经存在，但是没有完成，先继续进行
            $reportSign = UserReportStrategy::fetReportSign($payreporting['step']);
            return RestResponseFactory::ok(['report_sign' => $reportSign]);
        } elseif ($vip && !$payreporting && !$reporting && !$report) {
            // 13您已经是VIP会员，可免费查询哦~
            $reportSign = UserReportConstant::PAY_TYPE_VIP_HAVE_FREE;
            return RestResponseFactory::ok(['report_sign' => $reportSign]);
        } elseif ($payReport || $report) {
            //免费、付费任一有效报告存在，报告已生成，是否重新查询
            $reportSign = UserReportConstant::REPORT_STEP_END;
            return RestResponseFactory::ok(['report_sign' => $reportSign]);
        } elseif (!$payreporting) {
            // 14普通用户，没有报告，直接支付
            $reportSign = UserReportConstant::PAY_TYPE_DIRECT_PAY;
            return RestResponseFactory::ok(['report_sign' => $reportSign]);
        }

        return RestResponseFactory::ok(['report_sign' => $reportSign]);
    }

    /**
     * 支付进行中 不能进行报告授权
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatusById(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;

        //付费进行中 不可以填写授权
        $data['payType'] = UserReportConstant::PAY_TYPE_PAY;
        $payReport = UserReportFactory::fetchReportingByIdAndType($data);
        //报告订单生成状态
        $res['report_status'] = 0;
        if ($payReport && $payReport['step'] == 0) {
            $res['report_status'] = 1;
        }

        //vip状态
        $vipTypeId = UserVipFactory::getVipTypeId();
        $vip = UserVipFactory::getVIPInfo($data['userId'], $vipTypeId);
        $res['vip_status'] = 0;
        if ($vip) {
            $res['vip_status'] = $vip['status'];
        }

        return RestResponseFactory::ok($res);
    }

    /**
     * 生成免费报告 && 获取用户认证信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchZhimaUserinfo(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //付费类型
        $data['payType'] = $request->input('payType', 1);
        //创建sd_user_report_task任务
        $data['carrierId'] = 0;
        $data['step'] = UserReportConstant::REPORT_STEP_START;
        $data['end_time'] = date('Y-m-d H:i:s', strtotime("+100 year"));
        $data['serialNum'] = PaymentStrategy::generateId(UserReportFactory::fetchReportTaskLastId(), 'REPORT');
        $data['front_serial_num'] = PaymentStrategy::generateFrontId(UserReportFactory::fetchReportTaskLastId());
        //添加免费查报告订单
        if ($data['payType'] == 1) {
            $reportTask = UserReportFactory::createOrUpdateReportTask($data);
            if (!$reportTask) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(2105), 2105);
            }
        }
        //认证通过7
        $data['face_status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FINAL;
        $user = UserIdentityFactory::fetchIdcardinfoById($data);
        if (!$user) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1130), 1130);
        }
        $params['realname'] = $user['realname'];
        $params['certificate_no'] = $user['certificate_no'];

        return RestResponseFactory::ok($params);
    }

    /**
     * 获取芝麻跳转地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchZhimaRoute(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['payType'] = $request->input('payType');
        //芝麻url地址
        $data['face_status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FINAL;
        $authen = UserIdentityFactory::fetchIdcardAuthenInfo($data);
        //用户未认证
        if (!$authen) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1130), 1130);
        }
        //判断是否有报告订单
        $data['step'] = UserReportConstant::REPORT_STEP_START;
        $report = UserReportFactory::fetchEfficationReportByIdAndStep($data);
        //未生成报告订单
        if (!$report) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(13005), 13005);
        }
        //芝麻需要数据
//        $params['userId'] = $data['userId'];
//        $params['pay_type'] = $data['payType'];
//        $params['name'] = isset($authen['realname']) ? $authen['realname'] : '';
//        $params['idcard'] = isset($authen['certificate_no']) ? $authen['certificate_no'] : '';
//        $params['mobile'] = UserFactory::fetchMobile($data['userId']);
//        $zhimaUrl = ZhimaService::i()->query($params);
//        return RestResponseFactory::ok(['zhima_url' => $zhimaUrl]);
        //不返回芝麻地址，直接返回速贷之家的h5页面
        return RestResponseFactory::ok(['zhima_url' => AppService::API_URL . '/view/users/report/zhima/url']);
    }

    /**
     * 前端轮循处理 查询芝麻处理状态
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchZhimaStep(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //付费类型
        $data['payType'] = $request->input('payType');
        //判断芝麻数据是否采集成功
//        $zhimaStep = UserReportFactory::fetchZhimaTaskById($data);
//        if ($zhimaStep != 2) {
//            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(13001), 13001);
//        }
        //同步芝麻数据责任链
//        $zhima = new DoZhimaHandler($data);
//        $res = $zhima->handleRequest();
//        //错误提示
//        if (isset($res['error'])) {
//            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
//        }
//
//        $params['zhima_sign'] = isset($res['zhima_sign']) ? $res['zhima_sign'] : 0;
        //芝麻跳转不需要进行判断
        $params['zhima_sign'] = 1;
        return RestResponseFactory::ok($params);
    }

    /**
     * 前端轮循处理   运营商数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrUpdateTask(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //付费类型
        $data['payType'] = $request->input('payType');
        $data['carrierTaskId'] = $request->input('carrierTaskId');
        $data['carrierStatus'] = $request->input('carrierStatus');
        $data['carrierStatusBool'] = $request->input('carrierStatusBool', 1);

        $carrier = new DoCarrierHandler($data);
        $res = $carrier->handleRequest();
        //错误信息提示
        if (isset($res['error'])) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), $res['error'], $res['code'], $res['error']);
        }

        return RestResponseFactory::ok(RestUtils::getStdObj());
    }

    /**
     * 报告生成中
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchProducting(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //付费类型
        $data['payType'] = $request->input('payType');
        //SLogger::getStream()->info('报告生成页面值', ['data' => $data]);
        $report = UserReportFactory::fetchNearReportByIdAndType($data);

        $params['report_task_id'] = 0;
        $params['report_sign'] = 0;
        if ($report && $report['step'] == 4) {
            //获取生成报告的id
            $data['step'] = $report['step'];
            //根据报告任务id查询报告
            $data['report_task_id'] = $report['id'];
            $reportinfo = UserReportFactory::fetchReportinfoPartById($data);
            //报告数据未采集成功
            if (empty($reportinfo['name'])) {
                return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(13006), 13006);
            }

            //得分为0时
            if (empty($reportinfo['final_score'])) {

                //根据报告id，根据速贷之家规则生成报告数据
                $data['reportinfo'] = $reportinfo;
                $res = new DoCreditIndustryHandler($data);
                $res = $res->handleRequest();
                if (isset($res['error'])) {
                    return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(13006), 13006);
                }

                //计算得分
                $res = new DoReportScoreHandler($data);
                $res = $res->handleRequest();
                if (!isset($res['error'])) {
                    $params['report_task_id'] = $report['id'];
                    $params['report_sign'] = 1;
                }
            } else {
                $params['report_task_id'] = $report['id'];
                $params['report_sign'] = 1;
            }
        }

        //SLogger::getStream()->info('新生成报告id', ['data' => $params]);
        //报告生成中
        return RestResponseFactory::ok($params);
    }

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
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1500), 1500);
        }
        //数据处理
        $report['list'] = UserReportStrategy::getReports($report['list']);
        $report['pageCount'] = $pageCount;

        return RestResponseFactory::ok($report);
    }

    /**
     * 验证单个报告进行步骤
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkReportStatusById(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        $data['reportTaskId'] = $request->input('reportTaskId');
        $data['payType'] = $request->input('payType');

        //免费查 vip过期 提示去支付vip金额
        $vipTypeId = UserVipFactory::getVipTypeId();
        $vip = UserVipFactory::getVIPInfo($data['userId'], $vipTypeId);
        if (!$vip && $data['payType'] == 1) {
            //11 表示不是vip 需要充值vip
            $reportSign = UserReportConstant::PAY_TYPE_NOT_VIP;
            return RestResponseFactory::ok(['report_sign' => $reportSign]);
        }

        //报告已过期
        $report = UserReportFactory::checkReportIsExpire($data);
        if (!$report) {
            //报告已过期
            $reportSign = UserReportConstant::REPORT_STEP_EXPIRE;
            return RestResponseFactory::ok(['report_sign' => $reportSign]);
        }

        $reportSign = UserReportStrategy::fetReportSign($report['step']);
        return RestResponseFactory::ok(['report_sign' => $reportSign]);
    }

    /**
     * 信用报告首页图片展示
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBanner()
    {
        //获取信用报告首页图片
        $banner = UserReportFactory::fetchReportBanner();

        //数据处理
        $params['img'] = QiniuService::getImgs($banner);
        return RestResponseFactory::ok($params);
    }

    /**
     * 信用报告 需要用户信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchReportUserinfo(Request $request)
    {
        $data['userId'] = $request->user()->sd_user_id;
        //认证通过9
        $data['face_status'] = UserIdentityConstant::AUTHENTICATION_STATUS_FINAL;
        $user = UserIdentityFactory::fetchIdcardinfoById($data);
        if (!$user) {
            return RestResponseFactory::ok(RestUtils::getStdObj(), RestUtils::getErrorMessage(1130), 1130);
        }
        $params['realname'] = $user['realname'];
        $params['certificate_no'] = $user['certificate_no'];

        return RestResponseFactory::ok($params);
    }

}