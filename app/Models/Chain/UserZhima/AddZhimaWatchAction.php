<?php

namespace App\Models\Chain\UserZhima;

use App\Helpers\Utils;
use App\Models\Chain\AbstractHandler;
use App\Models\Orm\UserZhima;
use App\Models\Orm\UserZhimaWatch;

class AddZhimaWatchAction extends AbstractHandler
{

    private $params = [];
    protected $error = ['error' => '更新行业白名单数据失败！', 'code' => 1003];

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第三步:更新行业白名单表
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updateZhimaWatch($this->params))
        {
            // 第四步: 更新芝麻任务表
            $this->setSuccessor(new UpdateZhimaTaskAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }

    // 插入数据
    private function updateZhimaWatch($params)
    {
        if (isset($params['watch']))
        {
            $watch = $params['watch'];
            $model = UserZhimaWatch::where('user_id', $params['userId'])->first();
            if (!$model)
            {
                $model = new UserZhimaWatch();
                $model->user_id = $watch['user_id'];
                $model->created_at = $watch['created_at'];
                $model->created_ip = $watch['created_ip'];
            }

            $model->is_matched = $watch['is_matched'];
            $model->biz_no = $watch['biz_no'];
            $model->details = $watch['details'];
            $model->updated_at = $watch['updated_at'];
            $model->updated_ip = $watch['updated_ip'];

            return $model->save();
        }

        return false;
    }
}