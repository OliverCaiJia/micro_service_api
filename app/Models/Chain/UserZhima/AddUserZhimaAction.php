<?php

namespace App\Models\Chain\UserZhima;

use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserZhima;

class AddUserZhimaAction extends AbstractHandler
{

    private $params = [];
    protected $error = ['error' => '更新芝麻信用表数据失败！', 'code' => 1002];

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第二步:更新芝麻信用表数据
     * @return array
     */
    public function handleRequest()
    {
        if ($this->addUserZhima($this->params))
        {
            // 更新白名单表
            $this->setSuccessor(new AddZhimaWatchAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    // 插入数据
    private function addUserZhima($params)
    {
        $openId = $params['openId'];
        // 先查找　找到更新　找不到则创建
        $model = UserZhima::where('open_id', $openId)->first();
        if ($model)
        {
            $model->phone = isset($params['phone']) ? $params['phone'] : '';
            $model->idcard = isset($params['idcard']) ? $params['idcard'] : '';
            $model->name = isset($params['name']) ? $params['name'] : '';
            $model->score = $params['score_new'];
            $model->updated_at = date('Y-m-d H:i:s', time());
            $model->updated_ip = Utils::ipAddress();
        }
        else
        {
            $model = new UserZhima();
            $model->user_id = intval($params['userId']);
            $model->open_id = $params['openId'];
            $model->score =  $params['score_new'];
            $model->phone = isset($params['phone']) ? $params['phone'] : '';
            $model->idcard = isset($params['idcard']) ? $params['idcard'] : '';
            $model->name = isset($params['name']) ? $params['name'] : '';
            $model->updated_at = date('Y-m-d H:i:s', time());
            $model->updated_ip = Utils::ipAddress();
        }

        $res = $model->save();
        if (!$res)
        {
            return false;
        }

        return true;
    }
}