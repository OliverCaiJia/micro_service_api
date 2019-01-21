<?php

namespace App\Models\Chain\Product\ProductTag;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ProductFactory;
use App\Models\Chain\Product\ProductTag\UpdateBlackTagStatusAction;

class CreateBlackTagLogAction extends AbstractHandler
{
    private $params = array();
    protected $error = array('error' => '不想看产品标签流水插入失败!', 'code' => 1000);
    private $user = null;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 2.如果标签存在，遍历插入流水
     *      如果标签不存在，直接插入流水
     * @return array
     */
    public function handleRequest()
    {
        if ($this->createBlackTagLog($this->params) == true) {
            $this->setSuccessor(new UpdateBlackTagStatusAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 创建不想看产品标签流水
     * @param $params
     * @return bool|int
     */
    private function createBlackTagLog($params)
    {
        $res = 0;
        //标签不存在 直接插入流水
        if (empty($params['tagIdArr'])) {
            $params['tagId'] = 0;
            $res = ProductFactory::createProductBlackTagLog($params);
        } else {
            //标签存在，遍历插入流水
            foreach ($params['tagIdArr'] as $key => $val) {
                $params['tagId'] = $val;
                $res = ProductFactory::createProductBlackTagLog($params);
            }
        }

        return $res;
    }

}


