<?php

namespace App\Models\Chain\Urge;

use App\Helpers\Utils;
use App\Models\Factory\CreditFactory;
use App\Models\Orm\UserCredit;
use App\Models\Chain\AbstractHandler;

class UpdateCreditAction extends AbstractHandler
{

    private $params = array();
    protected $error = array('error' => '对不起,用户总积分减少失败！', 'code' => 6003);
    protected $data;

    public function __construct($params)
    {
        $this->params = $params;
    }


    /**
     * @return array|bool
     * 4.用户总积分减少
     */
    public function handleRequest()
    {
        if ($this->updateCredit($this->params) == true) {
            return true;
        } else {
            return $this->error;
        }
    }


    /**
     * 更新用户总积分
     * @param $params
     * @return bool
     */
    private function updateCredit($params)
    {
        //用户id
        $params['score'] = $params['expend'];
        return CreditFactory::reduceUserCredit($params);
    }
}
