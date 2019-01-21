<?php

namespace App\Models\Chain\Product\ProductTag;

use App\Models\Chain\AbstractHandler;
use App\Models\Factory\ProductFactory;
use App\Models\Chain\Product\ProductTag\UpdateBlackTagAction;

class UpdateBlackTagStatusAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '修改不想看标签状态失败!', 'code' => 1001);

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * 3.修改sd_user_product_black_tag，物理删除该用户，该产品下的所有标签
     * @return array
     */
    public function handleRequest()
    {
        if ($this->updateBlackTagStatus($this->params) == true) {
            $this->setSuccessor(new UpdateBlackTagAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 删除以前存在的不想看产品标签
     * @param $params
     * @return bool|int
     */
    private function updateBlackTagStatus($params)
    {
        //查询现有的不想看产品标签数量
        $params['conTagIds'] = ProductFactory::fetchBlackConTagIds($params);
        $this->params['conTagIds'] = $params['conTagIds'];
        //以前存在不想看产品标签，删除
        if (!empty($params['conTagIds'])) {
            //删除标签
            $deleteTag = ProductFactory::deletePeoductBlackTags($params);
        } else {
            $deleteTag = 1;
        }

        return $deleteTag;
    }


}
