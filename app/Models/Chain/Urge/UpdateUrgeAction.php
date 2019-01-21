<?php


namespace App\Models\Chain\Urge;

use App\Models\Chain\AbstractHandler;
use App\Models\Chain\Urge\CreateCreditLogAction;
use App\Models\Factory\ProductFactory;

class UpdateUrgeAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '修改催审状态失败！', 'code' => 6002);
    private $params = array();

    public function __construct($params)
    {
        $this->params = $params;
        $this->setSuccessor($this);
    }

    /**
     * @return mixed]
     * 2.修改催审状态
     */
    public function handleRequest()
    {
        if ($this->updateHistoryUrge($this->params) == true) {
            $this->setSuccessor(new CreateCreditLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }


    private function updateHistoryUrge($params)
    {
        return ProductFactory::updateHistoryUrge($params);
    }

}