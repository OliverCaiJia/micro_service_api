<?php

namespace App\Models\Factory;

use App\Constants\UserReportConstant;
use App\Helpers\Logger\SLogger;
use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\AccountPayment;
use App\Models\Orm\UserDatasocureTask;
use App\Models\Orm\UserOrder;
use App\Models\Orm\UserOrderType;
use App\Models\Orm\UserReport;
use App\Models\Orm\UserReportTask;
use App\Models\Orm\UserReportType;
use App\Models\Orm\UserZhima;
use App\Models\Orm\UserZhimaTask;
use App\Models\Orm\UserZhimaWatch;
use App\Strategies\PaymentStrategy;

/**
 * 用户信用报告工厂类
 * Class UserReportFactory
 * @package App\Models\Factory
 */
class UserReportFactory extends AbsModelFactory
{

    /**
     * 获取用户免费报告次数
     *
     * @param $userId
     * @return int
     */
    public static function getUserReportCount($userId)
    {
        $count = UserReportTask::where(['user_id' => $userId, 'pay_type' => 1])->count();

        return $count ? $count : 0;
    }

    /**
     * 创建报告任务
     *
     * @param $userId
     * @return bool
     */
    public static function createReportTask($userId)
    {
        $now = date('Y-m-d H:i:s', time());
        $message = UserReportTask::select(['id', 'step'])
            ->where(['user_id' => $userId, 'pay_type' => UserReportConstant::REPORT_TASK_IS_PAY, 'status' => 1])
            ->where('step', '!=', UserReportConstant::REPORT_STEP_EXPIRE)
            ->where('step', '!=', UserReportConstant::REPORT_STEP_END)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->first();
        SLogger::getStream()->info('最近时间报告任务是否存在', ['message' => $message, 'code' => 10200444]);
        if (!$message) {
            $message = new UserReportTask();
            $message->serial_num = PaymentStrategy::generateId(UserReportFactory::fetchReportTaskLastId(), 'REPORT');
            $message->front_serial_num = PaymentStrategy::generateFrontId(UserReportFactory::fetchReportTaskLastId());
            SLogger::getStream()->info('不存在情况', ['message' => $message, 'code' => 10200768]);
        }
        $message->user_id = $userId;
        $message->pay_type = UserReportConstant::REPORT_TASK_IS_PAY;
        $message->step = UserReportConstant::REPORT_TASK_STEP;
        $message->start_time = date('Y-m-d H:i:s', time());
        $message->end_time = date('Y-m-d H:i:s', strtotime('+100 year'));
        $message->created_at = date('Y-m-d H:i:s', time());
        $message->created_ip = Utils::ipAddress();

        return $message->save();
    }

    /**
     * 获取最后一个id
     *
     * @return int
     */
    public static function fetchReportTaskLastId()
    {
        $id = UserReportTask::where(['status' => 1])->orderBy('id', 'desc')->value('id');

        return $id ? $id : 1;
    }

    /**
     * 获取订单类型ID
     *
     * @return mixed|string
     */
    public static function fetchOrderType()
    {
        $id = UserOrderType::where(['type_nid' => UserReportConstant::REPORT_ORDER_TYPE])->value('id');

        return $id ? $id : 1;
    }

    /**
     * 获取支付渠道ID
     *
     * @return mixed|string
     */
    public static function fetchPaymentType()
    {
        $id = AccountPayment::where(['nid' => UserReportConstant::PAYMENT_TYPE])->value('id');

        return $id ? $id : 1;
    }

    /**
     * 获取报告金额
     *
     * @return int
     */
    public static function fetchReportPrice()
    {
        //根据报告类型获取价格
        $res = UserReportType::where(['type_nid' => UserReportConstant::REPORT_TYPE, 'status' => 1])->value('report_consume');

        return $res ? $res : 20;
    }

    /**
     * 报告有效期 默认30天
     * @return int
     */
    public static function fetchReportPeriod()
    {
        $res = UserReportType::where(['type_nid' => UserReportConstant::REPORT_TYPE, 'status' => 1])->value('report_period');

        return $res ? $res : 30;
    }

    /**
     * 信用报告首页图片
     * @return string
     */
    public static function fetchReportBanner()
    {
        $res = UserReportType::where(['type_nid' => UserReportConstant::REPORT_TYPE, 'status' => 1])->value('img');

        return $res ? $res : '';
    }

    /**
     * 根据用户id&付费类型&时间  查询报告状态
     * @user_id integer 用户id
     * @step integer 步骤 0 任务开始, 1 芝麻完毕, 2 运营商处理完毕 3 报告处理中, 4 报告生成完毕, 9过期
     * @type integer 查询报告类型
     * @param array $params
     * @return array
     */
    public static function fetchReportByIdAndType($params = [])
    {
        $now = date('Y-m-d H:i:s', time());
        $report = UserReportTask::select(['id', 'step'])
            ->where(['user_id' => $params['userId'], 'pay_type' => $params['payType'], 'status' => 1])
            ->where('step', '!=', UserReportConstant::REPORT_STEP_EXPIRE)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->first();

        return $report ? $report->toArray() : [];
    }

    /**
     * 最近一条报告信息
     * @param array $params
     * @return array
     */
    public static function fetchNearReportByIdAndType($params = [])
    {
        $now = date('Y-m-d H:i:s', time());
        $report = UserReportTask::select(['id', 'step'])
            ->where(['user_id' => $params['userId'], 'pay_type' => $params['payType'], 'status' => 1])
            ->where('step', '!=', UserReportConstant::REPORT_STEP_EXPIRE)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->orderBy('created_at', 'desc')
            ->first();

        return $report ? $report->toArray() : [];
    }

    /**
     * * 根据用户id&付费类型&时间  查询报告未完成的报告状态
     * @user_id integer 用户id
     * @step integer 步骤 0 任务开始, 1 芝麻完毕, 2 运营商处理完毕 3 报告处理中, 4 报告生成完毕, 9过期
     * @type integer 查询报告类型
     * @param array $params
     * @return array
     */
    public static function fetchReportingByIdAndType($params = [])
    {
        $now = date('Y-m-d H:i:s', time());
        $report = UserReportTask::select(['id', 'step'])
            ->where(['user_id' => $params['userId'], 'pay_type' => $params['payType'], 'status' => 1])
            ->where('step', '!=', UserReportConstant::REPORT_STEP_EXPIRE)
            ->where('step', '!=', UserReportConstant::REPORT_STEP_END)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->first();

        return $report ? $report->toArray() : [];
    }

    /**
     * 有效报告
     * @step integer 步骤 0 任务开始, 1 芝麻完毕, 2 运营商处理完毕 3 报告处理中, 4 报告生成完毕, 9过期
     * @param array $params
     * @return array
     */
    public static function fetchEfficationReportByIdAndStep($params = [])
    {
        $now = date('Y-m-d H:i:s', time());
        $report = UserReportTask::select(['id', 'step', 'id', 'serial_num'])
            ->where(['user_id' => $params['userId'], 'pay_type' => $params['payType'], 'status' => 1])
            ->where(['step' => $params['step']])
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->first();

        return $report ? $report->toArray() : [];
    }

    /**
     * 最近
     * @param array $params
     * @return array
     */
    public static function fetchNearEfficationReportByIdAndStep($params = [])
    {
        $now = date('Y-m-d H:i:s', time());
        $report = UserReportTask::select(['id', 'step', 'id', 'serial_num'])
            ->where(['user_id' => $params['userId'], 'pay_type' => $params['payType'], 'status' => 1])
            ->where(['step' => $params['step']])
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->orderBy('created_at', 'desc')
            ->first();

        return $report ? $report->toArray() : [];
    }

    /**
     * 创建或修改信用报告任务表
     * @param array $params
     * @status integer 状态, 1有效, 0无效
     * @step integer 步骤 0 任务开始, 1 芝麻完毕, 2 运营商处理完毕 3 报告处理中, 4 报告生成完毕, 9过期
     * @return bool
     */
    public static function createOrUpdateReportTask($params = [])
    {
        $now = date('Y-m-d H:i:s', time());
        $query = UserReportTask::where(['user_id' => $params['userId'], 'pay_type' => $params['payType'], 'status' => 1])
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->where('step', '!=', UserReportConstant::REPORT_STEP_EXPIRE)
            ->where('step', '!=', UserReportConstant::REPORT_STEP_END)
            ->first();
        if (!$query) {
            $query = new UserReportTask();
            $query->serial_num = $params['serialNum'];
            $query->front_serial_num = $params['front_serial_num'];
            $query->created_at = date('Y-m-d H:i:s', time());
            $query->created_ip = Utils::ipAddress();
        }
        $query->user_id = $params['userId'];
        $query->carrier_task_id = $params['carrierId'];
        $query->status = 1;
        $query->step = $params['step'];
        $query->start_time = $now;
        $query->end_time = $params['end_time'];
        $query->pay_type = $params['payType'];
        $query->updated_at = $now;
        $query->updated_ip = Utils::ipAddress();

        return $query->save();
    }

    /**
     * `task_id` string '任务ID',
     * `status` integer '0,任务创建通知  1,任务登录通知  2,任务采集失败通知   3,账单通知   4,报告通知',
     * `status_bool` integer '1,true  0,false',
     * @param array $params
     * @return bool
     */
    public static function createOrUpdateCarrierTask($params = [])
    {
        $query = UserDatasocureTask::select()
            ->where(['user_id' => $params['userId'], 'task_id' => $params['carrierTaskId']])
            ->first();
        if (!$query) {
            $query = new UserDatasocureTask();
            $query->created_at = date('Y-m-d H:i:s', time());
        }

        $query->user_id = $params['userId'];
        $query->task_id = $params['carrierTaskId'];
        $query->status = $params['carrierStatus'];
        $query->status_bool = isset($params['carrierStatusBool']) ? $params['carrierStatusBool'] : 1;
        $query->mobile = isset($params['carrierMobile']) ? $params['carrierMobile'] : '';
        $query->message = isset($params['message']) ? $params['message'] : '';
        $query->updated_at = date('Y-m-d H:i:s', time());

        return $query->save();
    }

    /**
     * 根据userId&运营商task_id  获取运营商主键id
     * @param array $params
     * @return int
     */
    public static function fetchCarrierIdByTaskId($params = [])
    {
        $query = UserDatasocureTask::select(['id'])
            ->where(['user_id' => $params['userId'], 'task_id' => $params['carrierTaskId']])
            ->first();

        return $query ? $query->id : 0;
    }

    /**
     * 根据用户id&修改时间倒叙  查询芝麻采集数据步骤
     * @param array $params
     * @return int
     */
    public static function fetchZhimaTaskById($params = [])
    {
        $query = UserZhimaTask::select(['step'])
            ->where(['user_id' => $params['userId']])
            ->orderBy('updated_at', 'desc')
            ->limit(1)
            ->first();

        return $query ? $query->step : 0;
    }

    /**
     * 信用任务id对应的信用报告信息
     * @param array $params
     * @return array
     */
    public static function fetchReportinfoById($params = [])
    {
        $report_task_id = $params['reportTaskId'];
        $query = UserReport::select()
            ->where(['user_id' => $params['userId']]);

        //report_task_id存在按id查询 report_task_id不存在查最近的一份有效报告
        if (empty($report_task_id)) {
            //最近的一份
            $query->orderBy('serial_num', 'desc')->orderBy('id', 'desc')->limit(1);
        } else {
            //按id进行查询
            $query->where(['report_task_id' => $report_task_id]);

        }
        $info = $query->first();

        return $info ? $info->toArray() : [];
    }

    /**
     * 信用报告订单列表
     * @param array $params
     * @return array
     */
    public static function fetchReports($params = [])
    {
        $pageSize = $params['pageSize'];
        $pageNum = $params['pageNum'];

        $query = UserReportTask::select(['id', 'user_id', 'serial_num', 'pay_type', 'step', 'end_time', 'front_serial_num', 'start_time'])
            ->where(['user_id' => $params['userId'], 'status' => 1]);

        //按查询类型筛选
        $payType = $params['payType'];
        $query->when($payType, function ($query) use ($payType) {
            $query->where(['pay_type' => $payType]);
        });

        //排序
        $query->orderBy('front_serial_num', 'desc')->orderBy('id', 'desc');

        /* 分页start */
        $count = $query->count();
        $countPage = ceil($count / $pageNum);
        if ($pageSize > $countPage) {
            $pageSize = $countPage;
        }
        $offset = ($pageSize - 1) * $pageNum;
        $limit = $pageNum;
        /* 分页end */

        $reports = $query
            ->limit($limit)
            ->offset($offset)
            ->get()->toArray();

        $resData['list'] = $reports;
        $resData['pageCount'] = $countPage ? $countPage : 0;

        return $resData ? $resData : [];

    }

    /**
     * 验证报告是否过期
     * @param array $params
     * @return array
     */
    public static function checkReportIsExpire($params = [])
    {
        $now = date('Y-m-d H:i:s', time());
        $report = UserReportTask::select(['id', 'step'])
            ->where(['user_id' => $params['userId'], 'status' => 1, 'id' => $params['reportTaskId']])
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->first();

        return $report ? $report->toArray() : [];
    }

    /**
     * 根据用户id获取用户芝麻分
     * @param array $params
     * @return int
     */
    public static function fetchZhimaScoreById($params = [])
    {
        $score = UserZhima::select(['score'])
            ->where(['user_id' => $params['userId']])
            ->first();

        return $score ? $score->score : 0;
    }

    /**
     * 获取芝麻信息
     * @param array $params
     * @return array
     */
    public static function fetchZhimaById($params = [])
    {
        $score = UserZhima::select(['score'])
            ->where(['user_id' => $params['userId']])
            ->first();

        return $score ? $score->toArray() : [];
    }

    /**
     * 芝麻行业关注名单
     * @param array $params
     * @return array
     */
    public static function fetchZhimaWatchDetailsById($params = [])
    {
        $details = UserZhimaWatch::select(['details'])
            ->where(['user_id' => $params['userId']])
            ->first();

        return $details ? $details->details : [];
    }

    /**
     * 创建或修改sd_user_report表
     * @param array $params
     * @return bool
     */
    public static function createOrUpdateUserReport($params = [])
    {
        $report = UserReport::select()
            ->where(['user_id' => $params['userId'], 'report_task_id' => $params['report_task_id']])
            ->first();

        if (!$report) {
            $report = new UserReport();
            $report->serial_num = $params['serial_num'];
            $report->front_serial_num = $params['front_serial_num'];
            $report->created_at = date('Y-m-d H:i:s', time());
            $report->serial_num = $params['serial_num'];
        }

        $report->user_id = $params['userId'];
        $report->details = $params['details'];
        $report->score = $params['score'];
        $report->report_task_id = $params['report_task_id'];
        $report->updated_at = date('Y-m-d H:i:s', time());
        return $report->save();
    }

    /**
     * 根据id获取信用报告部分信息
     * @param array $params
     * @return array
     */
    public static function fetchReportinfoPartById($params = [])
    {
        $query = UserReport::select(['id', 'report_task_id', 'name', 'queried_infos'])
            ->where(['user_id' => $params['userId']])
            ->where(['report_task_id' => $params['report_task_id']])
            ->first();

        return $query ? $query->toArray() : [];
    }

    /**
     * 修改信用报告任务中结束时间 当前时间向后推一个月
     * @param array $params
     * @return bool
     */
    public static function updateUserReportTaskUpdatedAt($params = [])
    {
        $query = UserReportTask::where(['user_id' => $params['userId'], 'id' => $params['report_task_id']])
            ->update([
                'end_time' => $params['end_time'],
                'updated_at' => date('Y-m-d H:i:s', time()),
                'updated_ip' => Utils::ipAddress(),
            ]);

        return $query ? $query : false;
    }

    /**
     * 行业信贷分析数据
     * @param array $params
     * @return bool
     */
    public static function updateCreditIndustryAnalysis($params = [])
    {
        $query = UserReport::where(['user_id' => $params['userId'], 'id' => $params['id']])
            ->update([
                'credit_industry_analysis' => isset($params['res_json']) ? $params['res_json'] : '',
                'queried_infos_analysis' => isset($params['queried_infos_analysis']) ? $params['queried_infos_analysis'] : '',
                'updated_at' => date('Y-m-d H:i:s', time()),
            ]);

        return $query ? $query : false;
    }

    /**
     * 上次得积分
     * @param array $params
     * @return int
     */
    public static function fetchNearScoreById($params = [])
    {
        $query = UserReport::select(['id', 'report_task_id', 'name', 'final_score'])
            ->where(['user_id' => $params['userId']])
            ->where('id', '!=', $params['id'])
            ->orderBy('created_at', 'desc')
            ->where('final_score', '!=', 0)
            ->limit(1)
            ->first();

        return $query ? $query->final_score : 0;
    }

    /**
     * 修改得分值
     * @param $params
     * @return bool
     */
    public static function updateUserReportScore($params = [])
    {
        $query = UserReport::select(['id'])
            ->where(['user_id' => $params['userId'], 'id' => $params['id']])
            ->first();

        $query->final_score = !empty($params['finScore']) ? $params['finScore'] : UserReportConstant::REPORT_MIN_RANGE;
        $query->updated_at = date('Y-m-d H:i:s', time());
        $res = $query->save();

        return $res;
    }
}