<?php

namespace App\Models\Chain\Creditcard\ShadowApply;

use App\Models\Chain\AbstractHandler;
use App\Models\Orm\BankCreditcard;
use App\Models\Orm\BankCreditCardApplyLog;

/**
 * 第二步:记录信用卡申请流水
 */
class CreateCardApplyLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '对不起,信用卡申请流水记录失败！', 'code' => 1001);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     *
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->createCreditCardApplyLog($this->params)) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     * 创建申请记录
     * @param $params
     * @return bool
     */
    private function createCreditCardApplyLog($params)
    {
        $log = new BankCreditCardApplyLog();
        $log->bank_id = isset($params['bank_id']) ? $params['bank_id'] : 0;
        $log->card_id = isset($params['card_id']) ? $params['card_id'] : 0;
        $log->card_name = isset($params['card_name']) ? $params['card_name'] : '';
        $log->channel_id = isset($params['channel_id']) ? $params['channel_id'] : 0;
        $log->channel_title = isset($params['channel_title']) ? $params['channel_title'] : '';
        $log->channel_nid = isset($params['channel_nid']) ? $params['channel_nid'] : '';
        $log->user_agent = isset($params['user_agent']) ? $params['user_agent'] : '';
        $log->user_id = isset($params['user_id']) ? $params['user_id'] : 0;
        $log->username = isset($params['username']) ? $params['username'] : '';
        $log->mobile = isset($params['mobile']) ? $params['mobile'] : '';
        $log->user_ip = isset($params['user_ip']) ? $params['user_ip'] : '';
        $log->shadow_nid = isset($params['shadow_nid']) ? $params['shadow_nid'] : 'sudaizhijia';
        $log->created_at = $params['created_at'];

        return $log->save();
    }
}
