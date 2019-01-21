<?php

namespace App\Models\Chain\UserReport\ReportScore;

use App\Constants\UserReportConstant;
use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserReportFactory;

/**
 * 3.统计得分
 * Class UpdateCreditIndustryAnalysisAction
 * @package App\Models\Chain\UserReport\CreditIndustry
 */
class UpdateReportScoreAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '统计得分失败！', 'code' => 10002);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateReportScore($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }


    /**
     * @param array $params
     * @return array|bool
     */
    private function updateReportScore($params = [])
    {
        //粒度
        $grain = $params['grain'];
        //报告信息
        $info = $params['info'];
        //得分区间最小值
        $minRange = $params['minRange'];
        //得分区间最大值
        $maxRange = $params['maxRange'];


        //权值
        //黑名单
        $blacks = UserReportConstant::REPORT_BLACKS_WEIGHT;
        //注册信息
        $registes = UserReportConstant::REPORT_REGISTER_WEIGHT;
        //机构查询历史
        $historys = UserReportConstant::REPORT_HISTORY_WEIGHT;
        //公积金
        $funds = UserReportConstant::REPORT_FUND_WEIGHT;

        //dd($info);
        //黑名单得分
        $black_types = json_decode($info['black_types'], true);
        $blacksScore = self::getBlackTypesScore($black_types);
        $blacksScore = bcmul($blacksScore, $blacks, 3);

        //注册信息得分
        $analyze = json_decode($info['queried_analyze'], JSON_UNESCAPED_UNICODE);
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
            $registeData['org_type_count'] = $list ? count($list) : 0;
            $registeData['count'] = $count;
        } else {
            $registeData['org_type_count'] = 0;
            $registeData['count'] = 0;
        }
        $registeScore = self::getRegisterScore($registeData);
        $registeScore = bcmul($registeScore, $registes, 3);

        //机构查询历史得分
        $queriedInfos = json_decode($info['queried_infos'], true);
        //分类计算org_type出现次数 机构总个数
        $res = empty($queriedInfos) ? [] : array_count_values(array_column($queriedInfos, "org_type"));
        //近6个月内贷款申请次数
        $loanCntInfos = json_decode($info['queried_infos_analysis'], true);
        $historyData['count'] = empty($loanCntInfos) ? 0 : $loanCntInfos['loan_cnt_180d'];
        $historyData['org_type_count'] = empty($res) ? 0 : count($res);
        $historyScore = self::getRegisterScore($historyData);
        $historyScore = bcmul($historyScore, $historys, 3);

        //公积金得分
        $fundData['email'] = isset($params['email']) ? $params['email'] : '';
        $fundData['company'] = isset($params['company']) ? $params['company'] : '';
        $fundData['company_type'] = isset($params['company_type']) ? $params['company_type'] : '';
        $fundData['home_address'] = isset($params['home_address']) ? $params['home_address'] : '';
        $fundArr = empty(array_sum($fundData)) ? [] : $fundData;
        $fundScore = self::getFundScore($fundArr);
        $fundScore = bcmul($fundScore, $funds, 3);

        //最终得分
        $scores = $blacksScore + $registeScore + $historyScore + $fundScore;
        //SLogger::getStream()->info('乘粒度之前得分', ['data' => $scores]);
        //得分 * 粒度
        $scores = bcmul($scores, $grain, 3);
        //最近一次闪信得分
        $nearScore = UserReportFactory::fetchNearScoreById($params);
        $nearScore = empty($nearScore) ? $minRange : $nearScore;
        //SLogger::getStream()->info('最近一次闪信得分',['data'=>$nearScore]);
        //最终得分
        $finalScore['finalScore'] = bcadd($scores, $nearScore);
        $finalScore['minRange'] = $minRange;
        $finalScore['maxRange'] = $maxRange;
        $finScore = self::getFinalScore($finalScore);

        $params['finScore'] = $finScore;
        //SLogger::getStream()->info('最终得分', ['data' => $params]);
        $res = UserReportFactory::updateUserReportScore($params);
        //SLogger::getStream()->info('保存最终得分结果', ['data' => $res]);
        return $res;
    }

    /**
     * 黑名单得分
     * @param $black_types
     * @return int
     */
    private function getBlackTypesScore($black_types)
    {
        $score = 0;

        if (empty($black_types)) {
            return $score = 10;
        }

        foreach ($black_types as $key => $val) {
            if ($val == '信贷逾期') $score = 6;
            elseif ($val == '失信') $score = 4;
            elseif ($val == '网贷') $score = 2;
            elseif ($val == '欺诈') $score = 0;
            else $score = 0;
        }

        return $score;
    }

    /**
     * 注册信息得分
     * @param array $registeData
     * @return int
     */
    private static function getRegisterScore($registeData = [])
    {
        //机构数【暂不用】
        $org_type_count = intval($registeData['org_type_count']);
        //总数  机构数为注册总次数
        $count = intval($registeData['count']);

        $score = 0;
        //未命中
        if ($count == 0) $score = 10;
        //0< x＜9
        if ($count > 0 && $count < 9) $score = 9 - $count;
        //>=9
        if ($count >= 9) $score = 0;

        return $score;
    }

    /**
     * 公积金得分
     * @param array $fundArr
     * @return int
     */
    private static function getFundScore($fundArr = [])
    {
        if (empty($fundArr)) $score = 0;
        else $score = 10;

        return $score;
    }

    /**
     * 最终得分
     * @param array $finalScore
     * @return float|mixed
     */
    private static function getFinalScore($finalScore = [])
    {
        $finScore = floatval($finalScore['finalScore']);
        if ($finScore < $finalScore['minRange']) return $score = $finalScore['minRange'];
        elseif ($finScore > $finalScore['maxRange']) return $score = $finalScore['maxRange'];
        else return $score = $finScore;
    }
}
