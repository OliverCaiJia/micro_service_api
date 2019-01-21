<?php

namespace App\Models\Chain\Product\ProductTag;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ProductFactory;
use App\Models\Chain\Product\ProductTag\CheckBlackAction;

class UpdateBlackTagAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '添加不想看产品标签失败!', 'code' => 1004);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     *  4.如果标签存在，遍历修改
     *      如果标签不存在，直接插入数据
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->updateBlackTag($this->params) == true) {
            $this->setSuccessor(new CheckBlackAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 修改标签
     * @param $params
     * @return bool|int
     */
    private function updateBlackTag($params)
    {
        $tagIdArr = $params['tagIdArr'];
        $res = 0;

        //没有标签， 直接插入或是修改
        if (empty($params['tagIdArr'])) {
            $params['tagId'] = 0;
            return ProductFactory::updateProductBlackTag($params);
        } else {
            //循环遍历修改
            foreach ($tagIdArr as $key => $val) {
                $params['tagId'] = $val;
                $res = ProductFactory::updateProductBlackTag($params);
            }

            return $res;
        }
    }
}