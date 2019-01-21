<?php

namespace App\Models\Chain\Product\ProductTag;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ProductFactory;
use App\Models\Chain\Product\ProductTag\UpdateBlackAction;

class CheckBlackAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '不可重复点击!', 'code' => 1005);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 5.验证是否加入过黑名单
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->checkBlack($this->params) == true) {
            $this->setSuccessor(new UpdateBlackAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * @param $params
     * @return bool|int
     */
    private function checkBlack($params)
    {
        $status = ProductFactory::fetchProductBlackStatus($params);
        //不想看产品已添加
        if ($status == 1) {
            return false;
        }

        return true;
    }
}