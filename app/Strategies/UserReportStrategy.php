<?php

namespace App\Strategies;

use App\Constants\UserReportConstant;
use App\Constants\UserVipConstant;
use App\Helpers\DateUtils;
use App\Helpers\Logger\SLogger;
use App\Models\Factory\UserIdentityFactory;
use App\Models\Factory\UserReportFactory;
use App\Services\Core\Store\Qiniu\QiniuService;

/**
 * 用户信用报告策略
 * Class UserReportStrategy
 * @package App\Strategies
 */
class UserReportStrategy extends AppStrategy
{
    /**
     * 1,2状态累计加1，表示跳转步骤
     * 1 芝麻完毕, 2 运营商处理完毕 3 报告处理中, 4 报告生成完毕,
     * @param $sign
     * @return int
     */
    public static function fetReportSign($sign)
    {
        if ($sign != 4 && $sign != 99) {
            $sign += 1;
        } elseif ($sign == 99) {
            //运营商登录成功但是未采集数据
            $sign = 2;
        }

        return $sign;
    }

    /**
     * 获取易宝订单一些参数值
     *
     * @return array
     */
    public static function getYibaoOtherParams()
    {
        return [
            'amount' => UserReportFactory::fetchReportPrice() * 100, //金额
            'productname' => UserReportConstant::REPORT_MEMBER_NAME . ' - ' . UserReportConstant::REPORT_ORDER_PRODUCT_NAME, //产品名字
            'productdesc' => UserReportConstant::ORDER_DESC,  //产品描述
            'url_params' => UserReportConstant::REPORT_ORDER_TYPE  // 回调地址自己定的参数值
        ];
    }

    /**
     * 获取用户订单一些参数
     *
     * @return array
     */
    public static function getUserOrderOtherParams()
    {
        return [
            'order_type' => UserReportFactory::fetchOrderType(),  //订单类型
            'payment_type' => UserReportFactory::fetchPaymentType(),  //支付类型
            'amount' => UserReportFactory::fetchReportPrice(), //金额
        ];
    }

    /**
     * 信用报告列表数据处理
     * @param array $params
     * @return array
     */
    public static function getReports($params = [])
    {
        $now = date('Y-m-d H:i:s', time());
        foreach ($params as $key => $val) {
            $user = UserIdentityFactory::fetchIdcardAuthenInfo($val['user_id']);
            $params[$key]['username'] = isset($user['realname']) ? $user['realname'] : '';
            $params[$key]['idcard'] = isset($user['certificate_no']) ? UserIdentityStrategy::formatCertificateNo($user['certificate_no']) : '';
            $expire = strtotime($val['end_time']) - strtotime($now);
            $params[$key]['serial_num'] = isset($val['front_serial_num']) ? $val['front_serial_num'] : '';
            //dd($expire);
            if ($expire <= 0 || 9 == $val['step']) {
                //已过期
                $params[$key]['step_sign'] = 2;
            } elseif ($expire > 0 && $val['step'] == 3) {
                //生成中
                $params[$key]['step_sign'] = 3;
            } elseif ($expire > 0 && $val['step'] == 4) {
                //有效报告
                $params[$key]['step_sign'] = 4;
            } else {
                //继续查询
                $params[$key]['step_sign'] = 1;
            }

            //处理时间
            if ($val['step'] == 1 || $val['step'] == 2 || $val['step'] == 4 || $params[$key]['step_sign'] == 2) {
                $params[$key]['start_time'] = DateUtils::formatDate($val['start_time']);
            } else {
                $params[$key]['start_time'] = '';
            }

        }

        return $params ? $params : [];
    }

    /**
     * 信用报告详情数据处理
     * @param array $params
     * @return array
     */
    public static function getReportinfoById($params = [])
    {
        //dd($params);
        $data = [];
        //分数&等级
        $data['serial_num'] = isset($params['front_serial_num']) ? $params['front_serial_num'] : '';
        $data['update_date'] = DateUtils::formatDate($params['updated_at']);
        $sdScore = UserReportStrategy::getSdScoreByZhimaScore($params['final_score']);
        $data['score'] = $sdScore['score'];
        $data['loan_money'] = $sdScore['range'];
        $data['grade'] = $sdScore['grade'];

        //=====身份解析=============
        $data['identity']['name'] = isset($params['name']) ? $params['name'] : '';
        $data['identity']['gender'] = isset($params['gender']) ? $params['gender'] : '';
        $data['identity']['age'] = isset($params['age']) ? $params['age'] : '';
        $data['identity']['idcard'] = isset($params['idcard']) ? $params['idcard'] : '';
        //身份证号是否有效
        $data['identity']['valid_idcard'] = isset($params['idcard']) ? '有效' : '无效';
        //籍贯
        $data['identity']['idcard_location'] = isset($params['idcard_location']) ? $params['idcard_location'] : '';
        $data['identity']['mobile'] = isset($params['mobile']) ? $params['mobile'] : '';
        //手机号码归属地
        $data['identity']['mobile_location'] = isset($params['mobile_location']) ? $params['mobile_location'] : '';
        //手机所属运营商
        $data['identity']['carrier'] = isset($params['carrier']) ? $params['carrier'] : '';
        $data['identity'] = empty(array_sum($data['identity'])) ? [] : $data['identity'];

        //=====注册信息=============
        $analyze = json_decode($params['queried_analyze'], JSON_UNESCAPED_UNICODE);
        $count = 0;
        $list = [];
        if (!empty($analyze)) {
            foreach ($analyze as $value) {
                $list[] = [
                    'org_type' => $value['org_type'],
                    'loan_cnt_180d' => $value['loan_cnt_180d'],
                ];
                $count += $value['loan_cnt_180d'];
            }
            $data['register']['list'] = $list ? $list : [];
            $data['register']['count'] = $count;
        } else {
            $data['register'] = [];
        }
        //====机构查询历史============
        $historyInfo = json_decode($params['queried_infos'], JSON_UNESCAPED_UNICODE);
        if (!empty($historyInfo)) {
            //机构查询历史表格内容
            $data['history'] = $historyInfo;
        } else {
            $data['history'] = [];
        }

        //机构查询历史近15天内贷款申请次数
        if (empty($historyInfo)) {
            $data['history_loan_cnt']['loan_cnt_15d'] = '未知';
            $data['history_loan_cnt']['loan_cnt_30d'] = '未知';
            $data['history_loan_cnt']['loan_cnt_90d'] = '未知';
            $data['history_loan_cnt']['loan_cnt_180d'] = '未知';
        } else {
            //当前时间
            $loanCnt['now'] = date('Y-m-d', time());
            //查询历史时间
            $loanCnt['dates'] = array_column($historyInfo, 'date');

            $data['history_loan_cnt']['loan_cnt_15d'] = UserReportStrategy::getLoanCntCount($loanCnt, 15);
            $data['history_loan_cnt']['loan_cnt_30d'] = UserReportStrategy::getLoanCntCount($loanCnt, 30);
            $data['history_loan_cnt']['loan_cnt_90d'] = UserReportStrategy::getLoanCntCount($loanCnt, 60);
            $data['history_loan_cnt']['loan_cnt_180d'] = UserReportStrategy::getLoanCntCount($loanCnt, 180);
        }

        //机构分类统计
        if (!empty($historyInfo)) {
            //机构查询历史表格内容
            $data['history_queried'] = array_count_values(array_column($historyInfo, "org_type"));;
        } else {
            $data['history_queried'] = [];
        }

        //====黑名单=================
        //错误情况 ["失信","信用卡"] ["失信"]
        $type = json_decode($params['black_types'], true);
        $data['black']['type'] = empty($type) ? "" : implode(',', $type);
        $data['black']['is_phone_name'] = isset($params['mobile_name_in_blacklist']) ? $params['mobile_name_in_blacklist'] : 0;
        $data['black']['phone_name'] = $params['mobile_name_blacklist_updated_time'];
        $data['black']['is_idcard_name'] = isset($params['idcard_name_in_blacklist']) ? $params['idcard_name_in_blacklist'] : 0;
        $data['black']['idcard_name'] = $params['idcard_name_blacklist_updated_time'];
        $data['black']['contact_total'] = $params['direct_contact_count'];
        $data['black']['contact_black_count'] = $params['direct_black_count'];
        $data['black']['introduce_black_count'] = $params['introduce_black_count']; //引起黑名单的直接联系人数量
        $data['black']['introduce_black_ratio'] = $params['introduce_black_ratio']; //引起黑名单的直接联系人占比
        $data['black']['indirect_black_count'] = $params['indirect_black_count']; //间接联系人在黑名单数量
        if (empty($params['blacklist_record'])) {
            $data['black']['user_address'] = '';
            $data['black']['loan_money'] = '';
            $data['black']['already_money'] = '';
            $data['black']['overdue_money'] = '';
        } else {
            $record = json_decode($params['blacklist_record'], JSON_UNESCAPED_UNICODE);
            $data['black']['user_address'] = isset($record['address']) ? $record['address'] : '';
            $data['black']['loan_money'] = isset($record['capital']) ? $record['capital'] : '';
            $data['black']['already_money'] = isset($record['paid_amount']) ? $record['paid_amount'] : '';
            $data['black']['overdue_money'] = isset($record['overdue_amount']) ? $record['overdue_amount'] : '';
        }
        $data['black'] = empty(array_sum($data['black'])) ? [] : $data['black'];

        //=======芝麻金融信贷信息================
        $zhima = json_decode($params['credit_industry_analysis'], JSON_UNESCAPED_UNICODE);

        if ($zhima) {
            foreach ($zhima as $key => $val) {
                if ($val['biz_code'] == 'AA') {
                    //====金融信贷信息==============
                    $data['finance'][$key]['settlement'] = isset($val['settlement']) ? UserReportStrategy::formatZhimaSettlement($val['settlement']) : '';
                    $data['finance'][$key]['biz_code'] = isset($val['biz_code']) ? $val['biz_code'] : '';
                    $data['finance'][$key]['type'] = isset($val['type']) ? UserReportStrategy::formatZhimaType($val['type']) : '';
                    $data['finance'][$key]['level'] = isset($val['level']) ? UserReportStrategy::formatZhimaLevel($val['level']) : '';
                    $data['finance'][$key]['refresh_time'] = isset($val['refresh_time']) ? DateUtils::formatDate($val['refresh_time']) : '';
                    $data['finance'][$key]['code'] = isset($val['code']) ? UserReportStrategy::formatZhimaCode($val['code']) : '';
                    $status = isset($val['status']) ? $val['status'] : '';
                    $data['finance'][$key]['status'] = UserReportStrategy::formatZhimaStatus($status);
                    if ($val['extend_info']) {
                        foreach ($val['extend_info'] as $item => $value) {
                            if ($value['key'] == 'event_end_time_desc') {
                                $data['finance'][$key]['event_end_time_desc'] = $value['value'];
                            } elseif ($value['key'] == 'event_max_amt_code') {
                                $data['finance'][$key]['event_max_amt_code'] = UserReportStrategy::formatZhimaMaxMoney($value['value']);
                            }
                        }
                    }
                } elseif ($val['biz_code'] == 'AB') {
                    //====公检法=====================
                    $data['security'][$key]['settlement'] = isset($val['settlement']) ? UserReportStrategy::formatZhimaSecuritySettlement($val['settlement']) : '';
                    $data['security'][$key]['biz_code'] = isset($val['biz_code']) ? $val['biz_code'] : '';
                    $data['security'][$key]['type'] = isset($val['type']) ? UserReportStrategy::formatZhimaType($val['type']) : '';
                    $data['security'][$key]['level'] = isset($val['level']) ? UserReportStrategy::formatZhimaLevel($val['level']) : '';
                    $data['security'][$key]['refresh_time'] = isset($val['refresh_time']) ? DateUtils::formatDate($val['refresh_time']) : '';
                    $data['security'][$key]['code'] = isset($val['code']) ? UserReportStrategy::formatZhimaCode($val['code']) : '';
                    $status = isset($val['status']) ? $val['status'] : '';
                    $data['security'][$key]['status'] = UserReportStrategy::formatZhimaStatus($status);
                    if ($val['extend_info']) {
                        foreach ($val['extend_info'] as $item => $value) {
                            if ($value['key'] == 'event_end_time_desc') {
                                $data['security'][$key]['event_end_time_desc'] = $value['value'];
                            } elseif ($value['key'] == 'event_max_amt_code') {
                                $data['security'][$key]['max_money'] = UserReportStrategy::formatZhimaMaxMoney($value['value']);
                            }
                        }
                    }
                }

            }
        } else {
            $data['finance'] = [];
            $data['security'] = [];
        }

        //====公积金====================
        $funds['email'] = isset($params['email']) ? $params['email'] : '';
        $funds['company'] = isset($params['company']) ? $params['company'] : '';
        $funds['company_type'] = isset($params['company_type']) ? $params['company_type'] : '';
        $funds['home_address'] = isset($params['home_address']) ? $params['home_address'] : '';
        $data['funds'] = empty(array_sum($funds)) ? [] : $funds;

        //=====借记卡信息=================
        $debit_card['update_date'] = $params['debit_update_date'];
        $debit_card['card_amount'] = $params['debit_card_amount'];
        $debit_card['total_amount'] = $params['balance'];
        $debit_card['total_salary_income'] = $params['total_salary_income']; //工资收入
        $debit_card['total_loan_income'] = $params['total_loan_income']; //贷款收入
        $debit_card['total_income'] = $params['total_income']; //总收入
        $debit_card['total_outcome'] = $params['total_outcome']; //总支出
        $debit_card['total_consume_outcome'] = $params['total_consume_outcome']; //近一年消费支出:100~200
        $debit_card['total_loan_outcome'] = $params['total_loan_outcome']; //近一年还贷支出:100~200
        $data['debit_card'] = empty(array_sum($debit_card)) ? [] : $debit_card;

        //=====信用卡信息==============
        $credit_card['update_date'] = $params['update_date'];
        $credit_card['card_amount'] = $params['card_amount'];
        $credit_card['total_credit_limit'] = $params['total_credit_limit']; // 总信用额
        $credit_card['total_credit_available'] = $params['total_credit_available']; //总可用额
        $credit_card['overdue_times'] = $params['overdue_times']; //逾期次数
        $credit_card['overdue_months'] = $params['overdue_months']; //逾期月数
        $credit_card['overdue_months'] = $params['overdue_months']; //总收入
        $credit_card['max_credit_limit'] = $params['max_credit_limit']; //单一银行最高信用额
        $data['credit_card'] = empty(array_sum($credit_card)) ? [] : $credit_card;

        return $data;
    }

    /**
     * 根据芝麻分数获取速贷分数
     * 350-470    0-180    0.5万以下    E    极低    存在较大风险，建议提升信用
     * 471-530    181-270    0.5-1万    D-    较低    存在一定风险，建议提升信用
     * 531-590    271-360    1-3万    D+
     * 591-650    361-450    3-5万    C-    良好    信用良好，如需更大额度，可提升信用
     * 651-710    451-540    5-10万    C+
     * 711-770    541-630    10-20万    B-    优秀    信用优秀，如需更大额度，可提升信用
     * 771-830    631-720    20-25万    B+
     * 831-890    721-810    23-30万    A-    极好    信用极好，如需更大额度，可提升信用
     * 891-950    811-900    30万以上    A+
     * @param string $param
     * @return mixed
     */
    public static function getSdScoreByZhimaScore($param = '')
    {
        //$sdScore = round(($param - 350) * 1.5);
        $sdScore = empty($param) ? UserReportConstant::REPORT_MIN_RANGE : round($param);
        if ($sdScore < 0) {
            $sdScore = 0;
        }
        $data = [];
        if ($sdScore >= 0 && $sdScore <= 180) {
            $data['range'] = '0.5万元以下';
            $data['grade'] = 9;
        } elseif ($sdScore >= 181 && $sdScore <= 270) {
            $data['range'] = '0.5-1万元';
            $data['grade'] = 8;
        } elseif ($sdScore >= 271 && $sdScore <= 360) {
            $data['range'] = '1-3万元';
            $data['grade'] = 7;
        } elseif ($sdScore >= 361 && $sdScore <= 450) {
            $data['range'] = '3-5万元';
            $data['grade'] = 6;
        } elseif ($sdScore >= 451 && $sdScore <= 540) {
            $data['range'] = '5-10万元';
            $data['grade'] = 5;
        } elseif ($sdScore >= 541 && $sdScore <= 630) {
            $data['range'] = '10-20万元';
            $data['grade'] = 4;
        } elseif ($sdScore >= 631 && $sdScore <= 720) {
            $data['range'] = '20-25万元';
            $data['grade'] = 3;
        } elseif ($sdScore >= 721 && $sdScore <= 810) {
            $data['range'] = '23-30万元';
            $data['grade'] = 2;
        } elseif ($sdScore >= 811 && $sdScore <= 900) {
            $data['range'] = '30万元以上';
            $data['grade'] = 1;
        } else {
            $data['range'] = '';
            $data['grade'] = 0;
        }
        $data['score'] = $sdScore;

        return $data;
    }

    /**
     * 分享类型编码
     * @param string $param
     * @return string
     */
    public static function formatZhimaType($param = '')
    {
        $param = trim($param);
        switch ($param) {
            case 'AA001':
                $res = '逾期未还款';
                break;
            case 'AA002':
                $res = '套现';
                break;
            case 'AB001':
                $res = '被执行人';
                break;
            case 'SD001':
                $res = '逾期未还款';
                break;
            default:
                $res = '';
        }

        return $res;
    }

    /**
     * level 风险等级  1 低风险  2 中风险  3 高风险
     * @param string $param
     * @return string
     */
    public static function formatZhimaLevel($param = '')
    {
        $param = trim($param);
        switch ($param) {
            case '1':
                $res = '低风险';
                break;
            case '2':
                $res = '中风险';
                break;
            case '3':
                $res = '高风险';
                break;
            default:
                $res = '';
        }

        return $res;
    }

    /**
     * settlement 当前状态 T 当前不逾期 F 当前逾期
     * @param string $param
     * @return string
     */
    public static function formatZhimaSettlement($param = '')
    {
        $param = trim($param);
        switch ($param) {
            case 'T':
                $res = '当前不逾期';
                break;
            case 'F':
                $res = '当前逾期';
                break;
            default:
                $res = '未知';
        }

        return $res;
    }

    /**
     * settlement 当前状态
     * T 已履行
     * F 未履行
     * 空值 未知
     * @param string $param
     * @return string
     */
    public static function formatZhimaSecuritySettlement($param = '')
    {
        $param = trim($param);
        switch ($param) {
            case 'T':
                $res = '已履行';
                break;
            case 'F':
                $res = '未履行';
                break;
            default:
                $res = '未知';
        }

        return $res;
    }

    /**
     *  * 历史最大逾期天数
     * AA001001 逾期1-30天
     * AA001002 逾期31-60天
     * AA001003 逾期61-90天
     * AA001004 逾期91-120天
     * AA001005 逾期121-150天
     * AA001006 逾期151-180天
     * AA001007 逾期180天以上
     * AA001010 逾期1期
     * AA001011 逾期2期
     * AA001012 逾期3期
     * AA001013 逾期4期
     * AA001014 逾期5期
     * AA001015 逾期6期
     * AA001016 逾期6期以上
     * AA002001 严重逾期且套现（通过交易类平台套现且长期未还款）
     * AB001001 失信被执行人（亦称“老赖”）
     * AB001002 被执行人
     * @param string $param
     * @return string
     */
    public static function formatZhimaCode($param = '')
    {
        $param = trim($param);
        switch ($param) {
            case 'AA001001':
                $res = '逾期1期';
                break;
            case 'AA001002':
                $res = '逾期2期';
                break;
            case 'AA001003':
                $res = '逾期3期';
                break;
            case 'AA001004':
                $res = '逾期4期';
                break;
            case 'AA001005':
                $res = '逾期5期';
                break;
            case 'AA001006':
                $res = '逾期6期';
                break;
            case 'AA001007':
                $res = '逾期6期以上';
                break;
            case 'AA002001':
                $res = '严重逾期且套现（通过交易类平台套现且长期未还款）';
                break;
            case 'AB001001':
                $res = '失信被执行人（亦称“老赖”）';
                break;
            case 'AB001002':
                $res = '被执行人';
                break;
            default:
                $res = '';
        }

        return $res;
    }

    /**
     * 历史最大逾期金额（元）
     * M01 (0,500]
     * M02 (500,1000]
     * M03 (1000,2000]
     * M04 (2000,3000]
     * M05 (3000,4000]
     * M06 (4000,6000]
     * M07 (6000,8000]
     * M08 (8000,10000]
     * M09 (10000,15000]
     * M10 (15000,20000]
     * M11 (20000,25000]
     * M12 (25000,30000]
     * M13 (30000,40000]
     * M14 (40000,∞)
     * 空值 未知
     * @param string $param
     * @return string
     */
    public static function formatZhimaMaxMoney($param = '')
    {
        $param = trim($param);
        switch ($param) {
            case 'M01':
                $res = '0-500';
                break;
            case 'M02':
                $res = '500-1000';
                break;
            case 'M03':
                $res = '1000-2000';
                break;
            case 'M04':
                $res = '2000-3000';
                break;
            case 'M05':
                $res = '3000-4000';
                break;
            case 'M06':
                $res = '4000-6000';
                break;
            case 'M07':
                $res = '6000-8000';
                break;
            case 'M08':
                $res = '8000-10000';
                break;
            case 'M09':
                $res = '10000-15000';
                break;
            case 'M010':
                $res = '15000-20000';
                break;
            case 'M011':
                $res = '20000-25000';
                break;
            case 'M012':
                $res = '25000-30000';
                break;
            case 'M013':
                $res = '30000-40000';
                break;
            case 'M014':
                $res = '40000-';
                break;
            default:
                $res = '';
        }

        return $res;
    }

    /**
     *  * status 异议状态
     * 0 原始状态
     * 1 用户有异议，信息核查中
     * 2 核查完毕，信息无误
     * 3 核查完毕，信息已修改
     * 5 用户对此记录有异议，但异议主张经核查未获支持
     * @param string $param
     * @return string
     */
    public static function formatZhimaStatus($param = '')
    {
        $param = trim($param);
        switch ($param) {
            case '0':
                $res = '原始状态';
                break;
            case '1':
                $res = '用户有异议，信息核查中';
                break;
            case '2':
                $res = '核查完毕，信息无误';
                break;
            case '3':
                $res = '核查完毕，信息已修改';
                break;
            case '5':
                $res = '用户对此记录有异议，但异议主张经核查未获支持';
                break;
            default:
                $res = '';
        }

        return $res;
    }

    /**
     * 根据导入账单逾期金额 计算风险等级
     * @param int $money
     * @return int
     */
    public static function getLevelByBillOverdueMoney($money = 0)
    {
        $money = floatval($money);
        //低风险（累计导入账单金额0-1000）
        if ($money >= 0 && $money <= 1000) return 1;
        //中风险1001-5000
        elseif ($money > 1000 && $money <= 5000) return 2;
        //高风险5001+
        elseif ($money > 5000) return 3;
        else return 0;
    }

    /**
     * * 历史最大逾期金额（元）
     * M01 (0,500]
     * M02 (500,1000]
     * M03 (1000,2000]
     * M04 (2000,3000]
     * M05 (3000,4000]
     * M06 (4000,6000]
     * M07 (6000,8000]
     * M08 (8000,10000]
     * M09 (10000,15000]
     * M10 (15000,20000]
     * M11 (20000,25000]
     * M12 (25000,30000]
     * M13 (30000,40000]
     * M14 (40000,∞)
     * 空值 未知
     * @param int $money
     * @return string
     */
    public static function getExtendInfo($money = 0)
    {
        $money = floatval($money);
        if ($money >= 0 && $money <= 500) return 'M01';
        elseif ($money >= 501 && $money <= 1000) return 'M02';
        elseif ($money > 1000 && $money <= 2000) return 'M03';
        elseif ($money > 2000 && $money <= 3000) return 'M04';
        elseif ($money > 3000 && $money <= 4000) return 'M05';
        elseif ($money > 4000 && $money <= 6000) return 'M06';
        elseif ($money > 6000 && $money <= 8000) return 'M07';
        elseif ($money > 8000 && $money <= 10000) return 'M08';
        elseif ($money > 10000 && $money <= 15000) return 'M09';
        elseif ($money > 15000 && $money <= 20000) return 'M10';
        elseif ($money > 20000 && $money <= 25000) return 'M11';
        elseif ($money > 25000 && $money <= 30000) return 'M12';
        elseif ($money > 30000 && $money <= 40000) return 'M13';
        elseif ($money > 40000) return 'M14';
        else return '';
    }

    /**
     * 时间范围内申请次数
     * @param array $params
     * @param int $time
     * @return int
     */
    public static function getLoanCntCount($params = [], $time = 0)
    {
        //当前时间
        $str_now = strtotime($params['now']);
        //规定时间之前的时间
        $before_time = strtotime($params['now'] . " - $time day");
//        dd(date('Y-m-d', $before_time));
        $count = 0;
        foreach ($params['dates'] as $key => $val) {
            if (strtotime($val) >= $before_time && strtotime($val) <= $str_now) {
                $count = $count + 1;
            }
        }

        return $count;
    }
}