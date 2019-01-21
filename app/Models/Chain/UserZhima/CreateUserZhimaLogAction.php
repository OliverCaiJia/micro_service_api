<?php

namespace App\Models\Chain\UserZhima;

use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserZhimaLog;

class CreateUserZhimaLogAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '插入芝麻信用流水表失败！', 'code' => 1001);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * 第一步:将查询结果插入芝麻信用流水表
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->createUserZhimaLog($this->params)) {
            $this->setSuccessor(new AddUserZhimaAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    private function createUserZhimaLog($params)
    {
        $model = new UserZhimaLog();
        $model->user_id = $params['userId'];
        $model->transaction_id = $params['transactionId'];
        $model->open_id = $params['openId'];
        $model->score_old = $params['score_old'];
        $model->score_new = $params['score_new'];
        $model->created_at = date('Y-m-d H:i:s', time());
        $model->identity_type = $params['identityType'];
        $model->phone = isset($params['phone']) ? $params['phone'] : '';
        $model->idcard = isset($params['idcard']) ? $params['idcard'] : '';
        $model->name = isset($params['name']) ? $params['name'] : '';
        $model->created_ip = Utils::ipAddress();
        return $model->save();

    }
}
