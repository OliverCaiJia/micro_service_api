<?php

namespace App\Models\Chain\UserReport\CreditIndustry;

use App\Helpers\Logger\SLogger;
use App\Models\Chain\AbstractHandler;
use App\Models\Factory\UserBillFactory;
use App\Models\Factory\UserBillPlatformFactory;
use App\Models\Factory\UserReportFactory;
use App\Strategies\UserReportStrategy;
use App\Models\Chain\UserReport\CreditIndustry\UpdateUserReportAction;

/**
 * 2. 修改报告任务表中结束时间 一个月有效
 * Class UpdateReportTaskTimeAction
 * @package App\Models\Chain\UserReport\CreditIndustry
 */
class UpdateReportTaskTimeAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '报告任务修改结束时间失败！', 'code' => 10002);
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updateReportTaskTime($this->params) == true) {
            $this->setSuccessor(new UpdateUserReportAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    /**
     * {
     *
     * "biz_code":"AA", 行业类型编码        biz_code 行业类型编码  AB 公检法 || biz_code 行业类型编码 AA 金融信贷类
     * "code":"AA001001",            历史最大逾期天数
     * AA001001 逾期1-30天
     * AA001002 逾期31-60天
     * AA001003 逾期61-90天
     * AA001004 逾期91-120天
     * AA001005 逾期121-150天
     * AA001006 逾期151-180天
     * AA001007 逾期180天以上
     * "extend_info":[                        info 扩展信息：event_max_amt_code 逾期金额（元）
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
     * {
     * "description":"逾期金额（元）",                    extend_info 扩展信息：id 编号
     * 该条数据的唯一id
     * extend_info 扩展信息：event_end_time_desc 违约时间
     * 历史最大逾期开始时间，只输出到年月份（YYYY-MM），不精确到日；未知时输出空值
     * "key":"event_max_amt_code",
     * "value":"M01"
     * },
     * {
     * "description":"编号",
     * "key":"id",
     * "value":"186e06538fccb2299e2b2c26d54bd597"
     * },
     * {
     * "description":"违约时间",
     * "key":"event_end_time_desc",
     * "value":"2016-12"
     * }
     * ],
     * "level":1,                    level 风险等级
     * 1 低风险
     * 2 中风险
     * 3 高风险
     * "refresh_time":"2017-01-21 05:48:46",            refresh_time 信息更新时间  数据更新的时间
     * "settlement":true,            settlement 当前状态
     * T 当前不逾期
     * F 当前逾期
     * 空值 未知
     * "type":"AA001"                        type 风险类型编码  AA002 套现  || type 风险类型编码 AA001 逾期未还款
     * }
     * @param array $params
     * @return bool
     */
    private function updateReportTaskTime($params = [])
    {
        //dd($params);
        $data = [];
        //biz_code 行业类型编码  AB 公检法 || biz_code 行业类型编码 AA 金融信贷类
        $data['biz_code'] = 'AA';

        //type 风险类型编码  SD002 套现  || type 风险类型编码 SD001 逾期未还款
        $data['type'] = 'SD001';
        //风险等级：低风险（累计导入账单金额0-1000）  中风险1001-5000  高风险5001+
        //根据导入账单的欠债金额，算范围
        //该用户下所有账户
        $billPlatformIds = UserBillPlatformFactory::fetchNearImportBillPlatformIds($params['userId']);
        //该账户下所有账单id
        $params['billIds'] = UserBillFactory::fetchRelBillIdsByPlatformIds($billPlatformIds);
        //该用户下所有逾期账单金额
        $billOverdueMoney = UserBillFactory::fetchBillOverdueMoney($params);
        $data['level'] = UserReportStrategy::getLevelByBillOverdueMoney($billOverdueMoney);
        //更新时间：2016-01-01（当前日期）
        $data['refresh_time'] = UserBillPlatformFactory::fetchNearImportBillPlatformTime($params['userId']);
        //当前状态：当前无逾期/当前有逾期
        //"settlement":true,   settlement 当前状态 T 当前不逾期 F 当前逾期
        $data['settlement'] = empty($billOverdueMoney) ? 'T' : 'F';

        //逾期最大金额：100-500/500-1000/1000-5000/5000-10000
        $data['extend_info'][0]['description'] = '逾期金额（元）';
        $data['extend_info'][0]['key'] = 'event_max_amt_code';
        $data['extend_info'][0]['value'] = UserReportStrategy::getExtendInfo($billOverdueMoney);

        $res[] = $data;
        $this->params['res_json'] = json_encode($res);

        $term = UserReportFactory::fetchReportPeriod();
        $params['end_time'] = date('Y-m-d H:i:s', strtotime('+' . $term . 'day'));

        //修改信用报告任务中结束时间 当前时间向后推一个月
        $update = UserReportFactory::updateUserReportTaskUpdatedAt($params);
        //SLogger::getStream()->info('机构信息分类统计', ['data' => $update]);
        return $update;
    }

}
