<?php

namespace App\Models\Chain\Product\ProductTag;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ProductFactory;

class UpdateBlackAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '加入黑名单失败!', 'code' => 1006);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     *  6.加入黑名单
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateBlack($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }

    /**
     *
     * @param $params
     * @return bool|int
     */
    private function updateBlack($params)
    {
        //创建产品黑名单
        $black = ProductFactory::updateProductBlack($params);
        if ($black) {
            return true;
        }

        return false;
    }
}