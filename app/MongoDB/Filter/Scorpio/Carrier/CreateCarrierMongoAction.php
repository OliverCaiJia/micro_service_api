<?php

namespace App\MongoDB\Filter\Scorpio;

use App\MongoDB\Factory\Credit\CarrierFactory;
use App\MongoDB\Filter\AbstractHandler;
use App\Strategies\ScorpioStrategy;

/*
 * mongo存储
 */
class CreateCarrierMongoAction extends AbstractHandler
{
    #外部传参
    protected $error = array('error' => '操作mongodb错误！', 'code' => 1002);
    private $params = array();
    private $mark = '';

    public function __construct($params, $mark)
    {
        $this->params = $params;
        $this->mark = $mark;
    }


    /**
     * 第二步:mongodb记录
     * @return array|bool
     */
    public function handleRequest()
    {
        if ($this->createCarrierMongo($this->params, $this->mark)) {
            $this->setSuccessor(new CreateCarrierMysqlAction($this->params, $this->mark));
            return $this->getSuccessor()->handleRequest();
        } else {
            return $this->error;
        }
    }

    /**
     * 添加数据
     *
     * @param $params
     * @param string $mark   标识（咱们自己的平台）
     * @return mixed
     */
    private function createCarrierMongo($params, $mark)
    {
        //存入mongo中
        $data = ScorpioStrategy::dataStruct($params, $mark);
        CarrierFactory::createPhoneChannelInfoLog($data);

        return CarrierFactory::createPhoneChannelInfo($data, $mark);
    }
}
