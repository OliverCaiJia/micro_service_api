<?php

namespace App\Models\Chain\UserZhima;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ZhimaFactory;

class UpdateZhimaTaskAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '更新芝麻任务表失败！', 'code' => 1004);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 第四步:更新芝麻任务状态:处理完毕
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateUserZhimaTask($this->params)) {
            return true;
        } else {
            return $this->error;
        }
    }

    private function updateUserZhimaTask($params)
    {
        return ZhimaFactory::updateTaskStatus(['where' => 1, 'userId' => $params['userId'], 'step' => 2]);
    }
}
