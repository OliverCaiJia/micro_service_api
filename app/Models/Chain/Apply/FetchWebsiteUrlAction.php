<?php
namespace App\Models\Chain\Apply;

use App\Models\Chain\AbstractHandler;
use App\Strategies\OauthStrategy;

class FetchWebsiteUrlAction extends AbstractHandler
{
    private $params = array();
    protected $data = array();

    public function __construct($params)
    {
        $this->params = $params;
    }



    /**
     * 获取产品第三方地址
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->fetchWebsiteUrl($this->params) == true)
        {
            $this->setSuccessor(new CreateProductApplyLogAction($this->params));
            return $this->getSuccessor()->handleRequest();
        }
        else
        {
            return $this->error;
        }
    }


    /**
     *
     * @param $params
     * @return bool
     */
    private function fetchWebsiteUrl($params)
    {
        $urlArr = OauthStrategy::getWebsite($params);
        $this->params['urlArr'] = $urlArr;

        return $urlArr ? $urlArr : [];
    }
}